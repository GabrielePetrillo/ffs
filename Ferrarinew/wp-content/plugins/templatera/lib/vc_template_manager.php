<?php
/**
 * Main object for controls
 *
 * @package vas_map
 */
if ( ! class_exists( 'VcTemplateManager' ) ) {
	/**
	 * Class VcTemplateManager
	 */
	class VcTemplateManager {
		/**
		 * @var string
		 */
		protected $dir;
		/**
		 * @var string
		 */
		protected static $post_type = 'templatera';
		/**
		 * @var string
		 */
		protected static $meta_data_name = 'templatera';
		/**
		 * @var string
		 */
		protected $settings_tab = 'templatera';
		/**
		 * @var string
		 */
		protected $filename = 'templatera';
		/**
		 * @var bool
		 */
		protected $init = false;
		/**
		 * @var bool
		 */
		protected $current_post_type = false;
		/**
		 * @var string
		 */
		protected static $template_type = 'templatera_templates';
		/**
		 * @var array
		 */
		protected $settings = array(
			'assets_dir' => 'assets',
			'templates_dir' => 'templates',
			'template_extension' => 'tpl.php',
		);
		/**
		 * @var string
		 */
		protected static $vcWithTemplatePreview = '4.8';
		/**
		 * Plugin directory name required to append all required js/css files.
		 *
		 * @var string
		 */
		protected $plugin_dir;

		/**
		 * VcTemplateManager constructor.
		 * @param $dir
		 */
		public function __construct( $dir ) {
			$this->dir = empty( $dir ) ? dirname( dirname( __FILE__ ) ) : $dir; // Set dir or find by current file path.
			$this->plugin_dir = basename( $this->dir );
			add_filter( 'wpb_vc_js_status_filter', array(
				$this,
				'setJsStatusValue',
			) );
		}

		/**
		 * @static
		 * Singleton
		 *
		 * @param string $dir
		 *
		 * @return VcTemplateManager
		 */
		public static function getInstance( $dir = '' ) {
			static $instance = null;
			if ( null === $instance ) {
				$instance = new VcTemplateManager( $dir );
			}

			return $instance;
		}

		/**
		 * @static
		 * Install plugins.
		 * Migrate default templates into templatera
		 * @return void
		 */
		public static function install() {
			$migrated = get_option( 'templatera_migrated_templates' ); // Check is migration already performed
			if ( 'yes' !== $migrated ) {
				$templates = (array) get_option( 'wpb_js_templates' );
				foreach ( $templates as $template ) {
					if ( is_array( $template ) && isset( $template['name'], $template['template'] ) ) {
						self::create( $template['name'], $template['template'] );
					}
				}
				update_option( 'templatera_migrated_templates', 'yes' );
			}
		}

		/**
		 * @return string
		 */
		public static function postType() {
			return self::$post_type;
		}

		/**
		 * Initialize plugin data
		 * @return VcTemplateManager
		 * @throws \Exception
		 */
		public function init() {
			if ( $this->init ) {
				return $this;
			}
			$this->init = true;

			if ( current_user_can( 'manage_options' ) && 'export_templatera' === vc_get_param( 'action' ) ) {
				add_action( 'wp_loaded', array(
					$this,
					'export',
				) );
			}
			$this->createPostType();
			$this->initPluginLoaded(); // init filters/actions and hooks
			// Add vc template post type into the list of allowed post types for WPBakery Page Builder.
			if ( $this->isSamePostType() ) {
				add_action( 'admin_init', array(
					$this,
					'createMetaBox',
				), 1 );
				add_filter( 'vc_role_access_with_post_types_get_state', '__return_true' );
				add_filter( 'vc_role_access_with_backend_editor_get_state', '__return_true' );
				add_filter( 'vc_role_access_with_frontend_editor_get_state', '__return_false' );
				add_filter( 'vc_check_post_type_validation', '__return_true' );
				add_filter( 'vc_is_valid_post_type_be', '__return_true' );
				add_filter( 'vc_is_valid_post_type_fe', '__return_false' );
			}
			add_action( 'wp_loaded', array(
				$this,
				'createShortcode',
			) );

			return $this; // chaining.
		}

		/**
		 * Create tab on VC settings page.
		 *
		 * @param $tabs
		 *
		 * @return array
		 * @throws \Exception
		 */
		public function addTab( $tabs ) {
			if ( $this->isUserRoleAccessVcVersion() && ! vc_user_access()->part( 'templates' )->can()->get() ) {
				return $tabs;
			}
			$tabs[ $this->settings_tab ] = esc_html__( 'Templatera', 'templatera' );

			return $tabs;
		}

		/**
		 * Create tab fields. in WPBakery Page Builder settings page options-general.php?page=vc_settings
		 *
		 * @param Vc_Settings $settings
		 */
		public function buildTab( Vc_Settings $settings ) {
			$settings->addSection( $this->settings_tab );
			add_filter( 'vc_setting-tab-form-' . $this->settings_tab, array(
				$this,
				'settingsFormParams',
			) );
			$settings->addField( $this->settings_tab, esc_html__( 'Export VC Templates', 'templatera' ), 'export', array(
				$this,
				'settingsFieldExportSanitize',
			), array(
				$this,
				'settingsFieldExport',
			) );
			$settings->addField( $this->settings_tab, esc_html__( 'Import VC Templates', 'templatera' ), 'import', array(
				$this,
				'settingsFieldImportSanitize',
			), array(
				$this,
				'settingsFieldImport',
			) );
		}

		/**
		 * Custom attributes for tab form.
		 * @param $params
		 *
		 * @return string
		 * @see VcTemplateManager::buildTab
		 *
		 */
		public function settingsFormParams( $params ) {
			$params .= ' enctype="multipart/form-data"';

			return $params;
		}

		/**
		 * Sanitize export field.
		 * @return bool
		 */
		public function settingsFieldExportSanitize() {
			return false;
		}

		/**
		 * Builds export link in settings tab.
		 */
		public function settingsFieldExport() {
			echo '<a href="export.php?page=wpb_vc_settings&action=export_templatera" class="button">' . esc_html__( 'Download Export File', 'templatera' ) . '</a>';
		}

		/**
		 * Convert template/post to xml for export
		 *
		 * @param stdClass $template
		 *
		 * @return string
		 */
		private function templateToXml( $template ) {
			$id = $template->ID;
			$meta_data = get_post_meta( $id, self::$meta_data_name, true );
			$post_types = isset( $meta_data['post_type'] ) ? $meta_data['post_type'] : false;
			$user_roles = isset( $meta_data['user_role'] ) ? $meta_data['user_role'] : false;
			$xml = '';
			$xml .= '<template>';
			$xml .= '<title>' . apply_filters( 'the_title_rss', $template->post_title ) . '</title>' . '<content>' . $this->wxr_cdata( apply_filters( 'the_content_export', $template->post_content ) ) . '</content>';
			if ( false !== $post_types ) {
				$xml .= '<post_types>';
				foreach ( $post_types as $t ) {
					$xml .= '<post_type>' . $t . '</post_type>';
				}
				$xml .= '</post_types>';
			}
			if ( false !== $user_roles ) {
				$xml .= '<user_roles>';
				foreach ( $user_roles as $u ) {
					$xml .= '<user_role>' . $u . '</user_role>';
				}
				$xml .= '</user_roles>';
			}

			$xml .= '</template>';

			return $xml;
		}

		/**
		 * Export existing template in XML format.
		 *
		 * @param int $id (optional) Template ID. If not specified, export all templates
		 */
		public function export() {
			$id = absint( vc_get_param( 'id' ) );
			if ( $id ) {
				$template = get_post( $id );
				if ( ! $template || self::postType() !== $template->post_type ) {
					die;
				}
				$templates = $template ? array( $template ) : array();
			} else {
				$templates = get_posts( array(
					'post_type' => self::postType(),
					'numberposts' => - 1,
				) );
			}

			$xml = '<?xml version="1.0"?><templates>';
			foreach ( $templates as $template ) {
				$xml .= $this->templateToXml( $template );
			}
			$xml .= '</templates>';
			header( 'Content-Description: File Transfer' );
			header( 'Content-Disposition: attachment; filename=' . $this->filename . '_' . date( 'dMY' ) . '.xml' );
			header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );
			// @codingStandardsIgnoreLine
			print $xml;
			die;
		}

		/**
		 * Import templates from file to the database by parsing xml file
		 * @return bool
		 */
		public function settingsFieldImportSanitize() {
			// @codingStandardsIgnoreLine
			$file = isset( $_FILES['import'] ) ? $_FILES['import'] : false;
			if ( ! $file || ! file_exists( $file['tmp_name'] ) ) {
				return false;
			} else {
				$post_types = get_post_types( array( 'public' => true ) );
				$roles = get_editable_roles();
				$templateras = simplexml_load_file( $file['tmp_name'] );
				foreach ( $templateras as $template ) {
					$template_post_types = $template_user_roles = $meta_data = array();
					$content = (string) $template->content;
					$id = $this->create( (string) $template->title, $content );
					$this->contentMediaUpload( $id, $content );
					foreach ( $template->post_types as $type ) {
						$post_type = (string) $type->post_type;
						if ( in_array( $post_type, $post_types, true ) ) {
							$template_post_types[] = $post_type;
						}
					}
					if ( ! empty( $template_post_types ) ) {
						$meta_data['post_type'] = $template_post_types;
					}
					foreach ( $template->user_roles as $role ) {
						$user_role = (string) $role->user_role;
						if ( in_array( $user_role, $roles, true ) ) {
							$template_user_roles[] = $user_role;
						}
					}
					if ( ! empty( $template_user_roles ) ) {
						$meta_data['user_role'] = $template_user_roles;
					}
					update_post_meta( (int) $id, self::$meta_data_name, $meta_data );
				}
				// @codingStandardsIgnoreLine
				@unlink( $file['tmp_name'] );
			}

			return false;
		}

		/**
		 * Build import file input.
		 */
		public function settingsFieldImport() {
			echo '<input type="file" name="import">';
		}

		/**
		 * Upload external media files in a post content to media library.
		 *
		 * @param $post_id
		 * @param $content
		 *
		 * @return bool
		 */
		protected function contentMediaUpload( $post_id, $content ) {
			preg_match_all( '/<img|a[^>]* src|href=[\'"]?([^>\'" ]+)/', $content, $matches );
			foreach ( $matches[1] as $match ) {
				$extension = pathinfo( $match, PATHINFO_EXTENSION );
				if ( ! empty( $match ) && ! empty( $extension ) ) {
					$file_array = array();
					$file_array['name'] = basename( $match );
					$tmp_file = download_url( $match );
					$file_array['tmp_name'] = $tmp_file;
					if ( is_wp_error( $tmp_file ) ) {
						return false;
					}
					$desc = $file_array['name'];
					$id = media_handle_sideload( $file_array, $post_id, $desc );
					if ( is_wp_error( $id ) ) {
						// @codingStandardsIgnoreLine
						@unlink( $file_array['tmp_name'] );

						return false;
					} else {
						$src = wp_get_attachment_url( $id );
					}
					$content = str_replace( $match, $src, $content );
				}
			}
			wp_update_post( array(
				'ID' => $post_id,
				'post_content' => $content,
			) );

			return true;
		}

		/**
		 * CDATA field type for XML
		 *
		 * @param $str
		 *
		 * @return string
		 */
		public function wxr_cdata( $str ) {
			if ( ! seems_utf8( $str ) ) {
				$str = utf8_encode( $str );
			}

			$str = '<![CDATA[' . str_replace( ']]>', ']]]]><![CDATA[>', $str ) . ']]>';

			return $str;
		}

		/**
		 * Create post type "templatera" and item in the admin menu.
		 * @return void
		 */
		public function createPostType() {
			register_post_type( self::postType(), array(
				'labels' => self::getPostTypesLabels(),
				'public' => false,
				'has_archive' => false,
				'show_in_nav_menus' => true,
				'exclude_from_search' => true,
				'publicly_queryable' => false,
				'show_ui' => true,
				'query_var' => true,
				'capability_type' => 'post',
				'hierarchical' => false,
				'menu_position' => null,
				'menu_icon' => $this->assetUrl( 'images/icon.gif' ),
				'show_in_menu' => ! WPB_VC_NEW_MENU_VERSION,
			) );
		}

		/**
		 * @return array
		 */
		public static function getPostTypesLabels() {
			return array(
				'add_new_item' => esc_html__( 'Add template', 'templatera' ),
				'name' => esc_html__( 'Templates', 'templatera' ),
				'singular_name' => esc_html__( 'Template', 'templatera' ),
				'edit_item' => esc_html__( 'Edit Template', 'templatera' ),
				'view_item' => esc_html__( 'View Template', 'templatera' ),
				'search_items' => esc_html__( 'Search Templates', 'templatera' ),
				'not_found' => esc_html__( 'No Templates found', 'templatera' ),
				'not_found_in_trash' => esc_html__( 'No Templates found in Trash', 'templatera' ),
			);
		}

		/**
		 * Init filters / actions hooks
		 */
		function initPluginLoaded() {
			load_plugin_textdomain( 'templatera', false, basename( $this->dir ) . '/locale' );

			// Check for nav controls
			add_filter( 'vc_nav_controls', array(
				$this,
				'createButtonFrontBack',
			) );
			add_filter( 'vc_nav_front_controls', array(
				$this,
				'createButtonFrontBack',
			) );

			// add settings tab in WPBakery Page Builder settings
			add_filter( 'vc_settings_tabs', array(
				$this,
				'addTab',
			) );
			// build settings tab @ER
			add_action( 'vc_settings_tab-' . $this->settings_tab, array(
				$this,
				'buildTab',
			) );

			add_action( 'vc_frontend_editor_enqueue_js_css', array(
				$this,
				'assetsFe',
			) );
			add_action( 'wp_ajax_vc_templatera_save_template', array(
				$this,
				'saveTemplate',
			) );
			add_action( 'wp_ajax_vc_templatera_delete_template', array(
				$this,
				'delete',
			) );
			add_filter( 'vc_templates_render_category', array(
				$this,
				'renderTemplateBlock',
			), 10, 2 );
			add_filter( 'vc_templates_render_template', array(
				$this,
				'renderTemplateWindow',
			), 10, 2 );

			if ( $this->getPostType() !== 'vc_grid_item' ) {
				add_filter( 'vc_get_all_templates', array(
					$this,
					'replaceCustomWithTemplateraTemplates',
				) );
			}
			add_filter( 'vc_templates_render_frontend_template', array(
				$this,
				'renderFrontendTemplate',
			), 10, 2 );
			add_filter( 'vc_templates_render_backend_template', array(
				$this,
				'renderBackendTemplate',
			), 10, 2 );
			add_action( 'vc_templates_render_backend_template_preview', array(
				$this,
				'getTemplateContentPreview',
			), 10, 2 );
			add_filter( 'vc_templates_show_save', array(
				$this,
				'addTemplatesShowSave',
			) );
			add_action( 'wp_ajax_wpb_templatera_load_html', array(
				$this,
				'loadHtml',
			) ); // used in changeShortcodeParams in templates.js, todo make sure we need this?
			add_action( 'save_post', array(
				$this,
				'saveMetaBox',
			) );
			add_action( 'vc_backend_editor_enqueue_js_css', array(
				$this,
				'assetsBe',
			) );

		}

		/**
		 * This used to detect what version of nav_controls use, and panels/modals js/template
		 *
		 * @param string $version
		 *
		 * @return bool
		 */
		function isNewVcVersion( $version = '4.4' ) {
			return defined( 'WPB_VC_VERSION' ) && version_compare( WPB_VC_VERSION, $version ) >= 0;
		}

		/**
		 * Removes save block if we editing templatera page
		 * In add templates panel window
		 * @param $show_save
		 * @return bool
		 * @since 4.4
		 */
		public function addTemplatesPanelShowSave( $show_save ) {
			if ( $this->isSamePostType() ) {
				$show_save = false; // we don't need "save" block if we editing templatera page.
			}

			return $show_save;
		}

		/**
		 * @return bool
		 * @since 4.4 we implemented new panel windows
		 */
		function isPanelVcVersion() {
			return $this->isNewVcVersion( '4.7' );
		}

		/**
		 * @return bool
		 * @since 4.8 we implemented new user roles part checks
		 */
		function isUserRoleAccessVcVersion() {
			return $this->isNewVcVersion( '4.8' );
		}

		/**
		 * Used to render template for backend
		 * @param $template_id
		 * @param $template_type
		 *
		 * @return string|int
		 * @since 4.4
		 *
		 */
		public function renderBackendTemplate( $template_id, $template_type ) {
			if ( self::$template_type === $template_type ) {
				WPBMap::addAllMappedShortcodes();
				// do something to return output of templatera template
				$post = get_post( $template_id );
				if ( $this->isSamePostType( $post->post_type ) ) {
					print $post->post_content;
					die();
				}
			}

			return $template_id;
		}

		/**
		 * Get template content for preview.
		 * @param $template_id
		 * @param $template_type
		 *
		 * @return string
		 * @since 4.5
		 *
		 */
		public function getTemplateContentPreview( $template_id, $template_type ) {
			if ( self::$template_type === $template_type ) {
				WPBMap::addAllMappedShortcodes();
				// do something to return output of templatera template
				$post = get_post( $template_id );
				if ( $this->isSamePostType( $post->post_type ) ) {
					return $post->post_content;
				}
			}

			return $template_id;
		}

		/**
		 * Used to render template for frontend
		 * @param $template_id
		 * @param $template_type
		 *
		 * @return string|int
		 * @since 4.4
		 *
		 */
		public function renderFrontendTemplate( $template_id, $template_type ) {
			if ( self::$template_type === $template_type ) {
				WPBMap::addAllMappedShortcodes();
				// do something to return output of templatera template
				$post = get_post( $template_id );
				if ( $this->isSamePostType( $post->post_type ) ) {
					vc_frontend_editor()->enqueueRequired();
					vc_frontend_editor()->setTemplateContent( $post->post_content );
					vc_frontend_editor()->render( 'template' );
					die();
				}
			}

			return $template_id;
		}

		/**
		 * @param $category
		 * @return mixed
		 */
		public function renderTemplateBlock( $category ) {
			if ( self::$template_type === $category['category'] ) {
				if ( ! $this->isUserRoleAccessVcVersion() || ( $this->isUserRoleAccessVcVersion() && vc_user_access()->part( 'templates' )->checkStateAny( true, null )->get() ) ) {
					$category['output'] = '
				<div class="vc_column vc_col-sm-12" data-vc-hide-on-search="true">
					<div class="vc_element_label">' . esc_html__( 'Save current layout as a template', 'templatera' ) . '</div>
					<div class="vc_input-group">
						<input name="padding" class="vc_form-control wpb-textinput vc_panel-templates-name" type="text" value=""
						       placeholder="' . esc_attr( 'Template name', 'templatera' ) . '">
						<span class="vc_input-group-btn"> <button class="vc_btn vc_btn-primary vc_btn-sm vc_template-save-btn">' . esc_html__( 'Save template', 'templatera' ) . '</button></span>
					</div>
					<span class="vc_description">' . esc_html__( 'Save your layout and reuse it on different sections of your website', 'templatera' ) . '</span>
				</div>';
				}
				$category['output'] .= '<div class="vc_col-md-12">';
				if ( isset( $category['category_name'] ) ) {
					$category['output'] .= '<h3>' . esc_html( $category['category_name'] ) . '</h3>';
				}
				if ( isset( $category['category_description'] ) ) {
					$category['output'] .= '<p class="vc_description">' . esc_html( $category['category_description'] ) . '</p>';
				}
				$category['output'] .= '</div>';
				$category['output'] .= '
			<div class="vc_column vc_col-sm-12">
			<ul class="vc_templates-list-my_templates">';
				if ( ! empty( $category['templates'] ) ) {
					foreach ( $category['templates'] as $template ) {
						$category['output'] .= visual_composer()->templatesPanelEditor()->renderTemplateListItem( $template );
					}
				}
				$category['output'] .= '</ul></div>';
			}

			return $category;
		}

		/**
		 * Hook templates panel window rendering, if template type is templatera_templates render it
		 * @param $template_name
		 * @param $template_data
		 *
		 * @return string
		 * @since 4.4
		 *
		 */
		public function renderTemplateWindow( $template_name, $template_data ) {
			if ( self::$template_type === $template_data['type'] ) {
				return $this->renderTemplateWindowTemplateraTemplates( $template_name, $template_data );
			}

			return $template_name;
		}

		/**
		 * Rendering templatera template for panel window
		 * @param $template_name
		 * @param $template_data
		 *
		 * @return string
		 * @since 4.4
		 *
		 */
		public function renderTemplateWindowTemplateraTemplates( $template_name, $template_data ) {
			ob_start();
			if ( $this->isNewVcVersion( self::$vcWithTemplatePreview ) ) {
				$template_id = esc_attr( $template_data['unique_id'] );
				$template_id_hash = md5( $template_id ); // needed for jquery target for TTA
				$template_name = esc_html( $template_name );
				$delete_template_title = esc_attr( 'Delete template', 'templatera' );
				$preview_template_title = esc_attr( 'Preview template', 'templatera' );
				$add_template_title = esc_attr( 'Add template', 'templatera' );
				$edit_template_title = esc_attr( 'Edit template', 'templatera' );
				$template_url = esc_attr( admin_url( 'post.php?post=' . $template_data['unique_id'] . '&action=edit' ) );
				$edit_tr_html = '';
				if ( ! $this->isUserRoleAccessVcVersion() || ( $this->isUserRoleAccessVcVersion() && vc_user_access()->part( 'templates' )->checkStateAny( true, null )->get() ) ) {
					$edit_tr_html = <<<EDTR
				<a href="$template_url"  class="vc_general vc_ui-control-button" title="$edit_template_title" target="_blank">
					<i class="vc_ui-icon-pixel vc_ui-icon-pixel-control-edit-dark"></i>
				</a>
				<button type="button" class="vc_general vc_ui-control-button" data-vc-ui-delete="template-title" title="$delete_template_title">
					<i class="vc_ui-icon-pixel vc_ui-icon-pixel-control-trash-dark"></i>
				</button>
EDTR;
				}

				print <<<HTML
			<button type="button" class="vc_ui-list-bar-item-trigger" title="$add_template_title"
					 	data-template-handler=""
						data-vc-ui-element="template-title">$template_name</button>
			<div class="vc_ui-list-bar-item-actions">
				<button type="button" class="vc_general vc_ui-control-button" title="$add_template_title"
					 	data-template-handler="">
					<i class="vc_ui-icon-pixel vc_ui-icon-pixel-control-add-dark"></i>
				</button>$edit_tr_html
				<button type="button" class="vc_general vc_ui-control-button" title="$preview_template_title"
					data-vc-container=".vc_ui-list-bar" data-vc-preview-handler data-vc-target="[data-template_id_hash=$template_id_hash]">
					<i class="vc_ui-icon-pixel vc_ui-preview-icon"></i>
				</button>
			</div>
HTML;
			} else {
				?>
				<div class="vc_template-wrapper vc_input-group"
				     data-template_id="<?php echo esc_attr( $template_data['unique_id'] ); ?>">
					<a data-template-handler="true" class="vc_template-display-title vc_form-control"
					   data-vc-ui-element="template-title"
					   href="javascript:;"><?php echo esc_html( $template_name ); ?></a>
					<span class="vc_input-group-btn vc_template-icon vc_template-edit-icon"
					      title="<?php esc_attr_e( 'Edit template', 'templatera' ); ?>"
					      data-template_id="<?php echo esc_attr( $template_data['unique_id'] ); ?>"><a
								href="<?php echo esc_attr( admin_url( 'post.php?post=' . $template_data['unique_id'] . '&action=edit' ) ); ?>"
								target="_blank" class="vc_icon"></i></a></span>
					<span class="vc_input-group-btn vc_template-icon vc_template-delete-icon"
					      title="<?php esc_attr_e( 'Delete template', 'templatera' ); ?>"
					      data-template_id="<?php echo esc_attr( $template_data['unique_id'] ); ?>"><i
								class="vc_icon"></i></span>
				</div>
				<?php
			}

			return ob_get_clean();
		}

		/**
		 * Function used to replace old my templates with new templatera templates
		 * @param array $data
		 *
		 * @return array
		 * @since 4.4
		 *
		 */
		public function replaceCustomWithTemplateraTemplates( array $data ) {
			$templatera_templates = $this->getTemplateList();
			$templatera_arr = array();
			foreach ( $templatera_templates as $template_name => $template_id ) {
				$templatera_arr[] = array(
					'unique_id' => $template_id,
					'name' => $template_name,
					'type' => 'templatera_templates',
					// for rendering in backend/frontend with ajax);
				);
			}

			if ( ! empty( $data ) ) {
				$found = false;
				foreach ( $data as $key => $category ) {
					if ( 'my_templates' === $category['category'] ) {
						$found = true;
						$data[ $key ]['templates'] = $templatera_arr;
					}
				}
				if ( ! $found ) {
					$data[] = array(
						'templates' => $templatera_arr,
						'category' => 'my_templates',
						'category_name' => esc_html__( 'My Templates', 'templatera' ),
						'category_description' => esc_html__( 'Append previously saved template to the current layout', 'templatera' ),
						'category_weight' => 10,
					);
				}
			} else {
				$data[] = array(
					'templates' => $templatera_arr,
					'category' => 'my_templates',
					'category_name' => esc_html__( 'My Templates', 'templatera' ),
					'category_description' => esc_html__( 'Append previously saved template to the current layout', 'templatera' ),
					'category_weight' => 10,
				);
			}

			return $data;
		}

		/**
		 * Maps Frozen row shortcode
		 * @throws \Exception
		 */
		function createShortcode() {
			vc_map( array(
				'name' => esc_html__( 'Templatera', 'templatera' ),
				'base' => 'templatera',
				'icon' => $this->assetUrl( 'images/icon32.gif' ),
				'category' => esc_html__( 'Content', 'templatera' ),
				'params' => array(
					array(
						'type' => 'dropdown',
						'heading' => esc_html__( 'Select template', 'templatera' ),
						'param_name' => 'id',
						'value' => array( esc_html__( 'Choose template', 'templatera' ) => '' ) + $this->getTemplateList(),
						'description' => esc_html__( 'Choose which template to load for this location.', 'templatera' ),
					),
					array(
						'type' => 'checkbox',
						'heading' => esc_html__( 'Use template scope for rendering', 'templatera' ),
						'param_name' => 'use_template_scope',
						'value' => array(
							esc_html__( 'Yes', 'js_composer' ) => 'yes',
						),
						'std' => '',
						'description' => esc_html__( 'If checked, then template scope used for custom fields. By default it uses selected post scope.', 'templatera' ),
					),
					array(
						'type' => 'textfield',
						'heading' => esc_html__( 'Extra class name', 'templatera' ),
						'param_name' => 'el_class',
						'description' => esc_html__( 'If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'templatera' ),
					),
				),
				'js_view' => 'VcTemplatera',
			) );
			add_shortcode( 'templatera', array(
				$this,
				'outputShortcode',
			) );
		}

		/**
		 * Frozen row shortcode hook.
		 *
		 * @param $atts
		 * @param string $content
		 *
		 * @return string
		 */
		public function outputShortcode( $atts, $content = '' ) {
			$id = '';
			$el_class = '';
			$output = '';
			$use_template_scope = '';
			extract( shortcode_atts( array(
				'el_class' => '',
				'id' => '',
				'use_template_scope' => '',
			), $atts ) );
			if ( empty( $id ) || 'templatera' !== get_post_type( $id ) ) {
				return $output;
			}
			WPBMap::addAllMappedShortcodes();
			$use_template_scope = apply_filters( 'templatera_use_template_scope', $use_template_scope, $id );
			if ( ! empty( $use_template_scope ) ) {
				$my_query = new WP_Query( array(
					'post_type' => self::postType(),
					'p' => (int) $id,
				) );
				global $post;
				$backup = $post;
				while ( $my_query->have_posts() ) {
					$my_query->the_post();
					if ( get_the_ID() === (int) $id ) {
						$output .= '<div class="templatera_shortcode' . ( $el_class ? ' ' . $el_class : '' ) . '">';
						ob_start();
						visual_composer()->addFrontCss();
						$content = get_the_content();
						// @codingStandardsIgnoreLine
						print $content;
						$output .= ob_get_clean();
						$output .= '</div>';
						$output = do_shortcode( $output );
					}
					wp_reset_postdata();
				}
				// @codingStandardsIgnoreLine
				$post = $backup;
			} else {
				$content = get_post_field( 'post_content', $id );
				if ( ! $content ) {
					return '';
				}
				$output = '';
				$output .= '<div class="templatera_shortcode' . ( $el_class ? ' ' . $el_class : '' ) . '">';
				ob_start();
				if ( $this->isNewVcVersion( '7.7' ) ) {
					if ( vc_modules_manager()->is_module_on( 'vc-custom-css' ) ) {
						vc_modules_manager()->get_module( 'vc-custom-css' )->output_custom_css_to_page();
					}
					visual_composer()->addShortcodesCss( $id );
				} elseif ( $this->isNewVcVersion( '7.6' ) ) {
					visual_composer()->addPageCustomCss( $id );
					visual_composer()->addShortcodesCss( $id );
				} else {
					visual_composer()->addPageCustomCss( $id );
					visual_composer()->addShortcodesCustomCss( $id );
				}
				$output .= ob_get_clean();
				$output .= do_shortcode( $content );
				$output .= '</div>';
			}

			if (vc_is_inline()) {
				wp_enqueue_style( 'templatera_inline', $this->assetUrl( 'css/front_style' . ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' ) . '.css' ), false, '2.1' );
			}

			return $output;
		}

		/**
		 * Create meta box for self::$post_type, with template settings
		 */
		public function createMetaBox() {
			add_meta_box( 'vc_template_settings_metabox', esc_html__( 'Template Settings', 'templatera' ), array(
				$this,
				'sideOutput',
			), self::postType(), 'side', 'high' );
		}

		/**
		 * Used in meta box VcTemplateManager::createMetaBox
		 */
		public function sideOutput() {
			$data = get_post_meta( get_the_ID(), self::$meta_data_name, true );
			$data_post_types = isset( $data['post_type'] ) ? $data['post_type'] : array();
			$post_types = get_post_types( array( 'public' => true ) );
			echo '<div class="misc-pub-section"><div class="templatera_title"><b>' . esc_html__( 'Post types', 'templatera' ) . '</b></div><div class="input-append">';
			foreach ( $post_types as $type ) {
				if ( 'attachment' !== $type && ! $this->isSamePostType( $type ) ) {
					echo '<label><input type="checkbox" name="' . esc_attr( self::$meta_data_name ) . '[post_type][]" value="' . esc_attr( $type ) . '" ' . ( in_array( $type, $data_post_types, true ) ? ' checked="true"' : '' ) . '>' . esc_html( ucfirst( $type ) ) . '</label><br/>';
				}
			}
			echo '</div><p>' . esc_html__( 'Select for which post types this template should be available. Default: Available for all post types.', 'templatera' ) . '</p></div>';
			$groups = get_editable_roles();
			$data_user_role = isset( $data['user_role'] ) ? $data['user_role'] : array();
			echo '<div class="misc-pub-section vc_user_role">
            <div class="templatera_title"><b>' . esc_html__( 'Roles', 'templatera' ) . '</b></div>
            <div class="input-append">';

			foreach ( $groups as $key => $g ) {
				echo '<label><input type="checkbox" name="' . esc_attr( self::$meta_data_name ) . '[user_role][]" value="' . esc_attr( $key ) . '" ' . ( in_array( $key, $data_user_role, true ) ? ' checked="true"' : '' ) . '> ' . esc_html( $g['name'] ) . '</label><br/>';
			}
			echo '</div><p>' . esc_html__( 'Select for user roles this template should be available. Default: Available for all user roles.', 'templatera' ) . '</p></div>';
		}

		/**
		 * Url to js/css or image assets of plugin
		 *
		 * @param $file
		 *
		 * @return string
		 */
		public function assetUrl( $file ) {
			return plugins_url( $this->plugin_dir . '/assets/' . $file, plugin_dir_path( dirname( __FILE__ ) ) );
		}

		/**
		 * Absolute path to assets files
		 *
		 * @param $file
		 *
		 * @return string
		 */
		public function assetPath( $file ) {
			return $this->dir . '/assets/' . $file;
		}

		/**
		 * @return bool
		 */
		public function isValidPostType() {
			$type = get_post_type();
			$post = $this->compareType( get_post_type( vc_get_param( 'post' ) ) );
			$post_type = $this->compareType( vc_get_param( 'post_type' ) );
			$post_type_id = $this->compareType( get_post_type( (int) vc_get_param( 'post_id' ) ) );
			$post_vc_type_id = $this->compareType( get_post_type( (int) vc_get_param( 'vc_post_id' ) ) );

			return ( ( $type && $this->compareType( $type ) ) || ( $post ) || ( $post_type ) || ( $post_type_id ) || ( $post_vc_type_id ) );
		}

		/**
		 * @param $type
		 * @return bool
		 */
		public function compareType( $type ) {
			return in_array( $type, array_merge( vc_editor_post_types(), array( 'templatera' ) ), true );
		}

		/**
		 * Load required js and css files
		 */
		public function assets() {
		}

		/**
		 *
		 * @throws \Exception
		 */
		public function assetsFe() {
			if ( $this->isValidPostType() && ( vc_user_access()->part( 'frontend_editor' )->can()->get() ) ) {
				$this->addGridScripts();
				$dependency = array( 'vc-frontend-editor-min-js' );
				$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
				wp_register_script( 'vc_plugin_inline_templates', $this->assetUrl( 'js/templates_panels' . $suffix . '.js' ), $dependency, WPB_VC_VERSION, true );
				wp_register_script( 'vc_plugin_templates', $this->assetUrl( 'js/templates' . $suffix . '.js' ), array(), time(), true );
				wp_localize_script( 'vc_plugin_templates', 'VcTemplateI18nLocale', array(
					'please_enter_templates_name' => esc_html__( 'Please enter template name', 'templatera' ),
				) );
				wp_register_style( 'vc_plugin_template_css', $this->assetUrl( 'css/style' . $suffix . '.css' ), false, '1.1.0' );
				wp_enqueue_style( 'vc_plugin_template_css' );
				$this->addTemplateraJs();
			}
		}

		/**
		 *
		 * @throws \Exception
		 */
		public function assetsBe() {
			if ( $this->isValidPostType() && ( vc_user_access()->part( 'backend_editor' )->can()->get() || $this->isSamePostType() ) ) {
				$this->addGridScripts();
				$dependency = array( 'vc-backend-min-js' );
				$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
				wp_register_script( 'vc_plugin_inline_templates', $this->assetUrl( 'js/templates_panels' . $suffix . '.js' ), $dependency, WPB_VC_VERSION, true );
				wp_register_script( 'vc_plugin_templates', $this->assetUrl( 'js/templates' . $suffix . '.js' ), array(), time(), true );
				wp_localize_script( 'vc_plugin_templates', 'VcTemplateI18nLocale', array(
					'please_enter_templates_name' => esc_html__( 'Please enter template name', 'templatera' ),
				) );
				wp_register_style( 'vc_plugin_template_css', $this->assetUrl( 'css/style' . $suffix . '.css' ), false, '1.1.0' );
				wp_enqueue_style( 'vc_plugin_template_css' );
				$this->addTemplateraJs();
			}
		}

		/**
		 * @return bool|false|string
		 */
		public function getPostType() {
			if ( $this->current_post_type ) {
				return $this->current_post_type;
			}
			$post_type = get_post_type();
			if ( empty( $post_type ) ) {
				if ( vc_get_param( 'post' ) ) {
					$post_type = get_post_type( (int) vc_get_param( 'post' ) );
				} elseif ( vc_get_param( 'post_type' ) ) {
					$post_type = vc_get_param( 'post_type' );
				}
			}
			$this->current_post_type = $post_type;

			return $this->current_post_type;
		}

		/**
		 * Create templates button on navigation bar of the Front/Backend editor.
		 *
		 * @param $buttons
		 *
		 * @return array
		 * @throws \Exception
		 */
		public function createButtonFrontBack( $buttons ) {
			if ( $this->isUserRoleAccessVcVersion() && ! vc_user_access()->part( 'templates' )->can()->get() ) {
				return $buttons;
			}
			if ( 'vc_grid_item' === $this->getPostType() ) {
				return $buttons;
			}

			$new_buttons = array();

			foreach ( $buttons as $button ) {
				if ( 'templates' !== $button[0] ) {
					// disable custom css as well but only in templatera page
					if ( ! $this->isSamePostType() || ( $this->isSamePostType() && 'custom_css' !== $button[0] ) ) {
						$new_buttons[] = $button;
					}
				} else {
					if ( $this->isPanelVcVersion() ) {
						// @since 4.4 button is available but "Save" Functionality in form is disabled in templatera post.
						$new_buttons[] = array(
							'custom_templates',
							'<li class="vc_navbar-border-right"><a href="#" class="vc_icon-btn vc_templatera_button"  id="vc-templatera-editor-button" title="' . esc_html__( 'Templates', 'templatera' ) . '"></a></li>',
						);
					} else {
						if ( ! $this->isSamePostType() ) {
							$new_buttons[] = array(
								'custom_templates',
								'<li class="vc_navbar-border-right"><a href="#" class="vc_icon-btn vc_templatera_button"  id="vc-templatera-editor-button" title="' . esc_html__( 'Templates', 'templatera' ) . '"></a></li>',
							);
						}
					}
				}
			}

			return $new_buttons;
		}

		/**
		 * Add javascript to extend functionality of templates editor panel or new panel(since 4.4)
		 */
		public function addEditorTemplates() {
		}

		/**
		 * Used to add js in backend/frontend to init template UI functionality
		 */
		public function addTemplateraJs() {
			wp_enqueue_script( 'vc_plugin_inline_templates' );
			wp_enqueue_script( 'vc_plugin_templates' );
		}

		/**
		 * Used to save new template from ajax request in new panel window
		 * @since 4.4
		 *
		 */
		public function saveTemplate() {
			if ( ! vc_verify_admin_nonce() || ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) ) {
				die();
			}
			$title = vc_post_param( 'template_name' );
			$content = vc_post_param( 'template' );
			$template_id = $this->create( $title, $content );
			visual_composer()->buildShortcodesCustomCss( $template_id );
			$template_title = get_the_title( $template_id );
			if ( $this->isNewVcVersion( self::$vcWithTemplatePreview ) ) {
				print visual_composer()->templatesPanelEditor()->renderTemplateListItem( array(
					'name' => $template_title,
					'unique_id' => $template_id,
					'type' => self::$template_type,
				) );
			} else {
				print $this->renderTemplateWindowTemplateraTemplates( $template_title, array( 'unique_id' => $template_id ) );
			}
			die();
		}

		/**
		 * Gets list of existing templates. Checks access rules defined by template author.
		 * @return array
		 */
		protected function getTemplateList() {
			require_once ABSPATH . 'wp-admin/includes/template.php';
			global $current_user;
			wp_get_current_user();
			$current_user_role = isset( $current_user->roles[0] ) ? $current_user->roles[0] : false;
			$list = array();
			$templates = get_posts( array(
				'post_type' => self::postType(),
				'numberposts' => - 1,
			) );
			$post = get_post( absint( vc_post_param( 'post_id' ) ) );
			foreach ( $templates as $template ) {
				$id = $template->ID;
				$meta_data = get_post_meta( $id, self::$meta_data_name, true );
				$post_types = isset( $meta_data['post_type'] ) ? $meta_data['post_type'] : array();
				$user_roles = isset( $meta_data['user_role'] ) ? $meta_data['user_role'] : array();
				if ( ( ! $post || ! $post_types || in_array( $post->post_type, $post_types, true ) ) && ( ! $current_user_role || ! $user_roles || in_array( $current_user_role, $user_roles, true ) ) ) {
					$list[ _draft_or_post_title( $template ) ] = $id;
				}
			}

			return $list;
		}

		/**
		 * Creates new template.
		 * @static
		 *
		 * @param $title
		 * @param $content
		 *
		 * @return int|WP_Error
		 */
		protected static function create( $title, $content ) {
			return wp_insert_post( array(
				'post_title' => $title,
				'post_content' => $content,
				'post_status' => 'publish',
				'post_type' => self::postType(),
			) );
		}

		/**
		 * Used to delete template by template id
		 *
		 * @param int $template_id - if provided used, if not provided used vc_post_param('template_id')
		 */
		public function delete( $template_id = null ) {
			if ( ! vc_verify_admin_nonce() || ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) ) {
				die();
			}
			$post_id = $template_id ? $template_id : absint( vc_post_param( 'template_id' ) );
			if ( ! is_null( $post_id ) ) {
				$post = get_post( $post_id );

				if ( ! $post || ! $this->isSamePostType( $post->post_type ) ) {
					die( 'failed to delete' );
				} elseif ( wp_delete_post( $post_id ) ) {
					die( 'deleted' );
				}
			}
			die( 'failed to delete' );
		}

		/**
		 * Saves post data in databases after publishing or updating template's post.
		 *
		 * @param $post_id
		 *
		 * @return bool
		 */
		public function saveMetaBox( $post_id ) {
			if ( ! $this->isSamePostType() ) {
				return true;
			}
			if ( isset( $_POST[ self::$meta_data_name ] ) ) {
				$options = isset( $_POST[ self::$meta_data_name ] ) ? (array) $_POST[ self::$meta_data_name ] : array();
				update_post_meta( (int) $post_id, self::$meta_data_name, $options );
			} else {
				delete_post_meta( (int) $post_id, self::$meta_data_name );
			}

			return true;
		}

		/**
		 * @param $value
		 *
		 * @return string
		 * @todo make sure we need this?
		 */
		public function setJsStatusValue( $value ) {
			return $this->isSamePostType() ? 'true' : $value;
		}

		/**
		 * Used in templates.js:changeShortcodeParams
		 * @todo make sure we need this
		 * Output some template content
		 * @todo make sure it is secure?
		 */
		public function loadHtml() {
			if ( ! vc_verify_admin_nonce() || ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) ) {
				die();
			}
			$id = vc_post_param( 'id' );
			$post = get_post( (int) $id );
			if ( ! $post ) {
				die( esc_html__( 'Wrong template', 'templatera' ) );
			}
			if ( $this->isSamePostType( $post->post_type ) ) {
				print $post->post_content;
			}
			die();
		}

		/**
		 *
		 */
		public function addGridScripts() {
			if ( $this->isSamePostType() ) {
				wp_enqueue_script( 'wpb_templatera-grid-id-param-js', $this->assetUrl( 'js/templatera-grid-id-param' . ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min' ) . '.js' ), array( 'vc-backend-actions-js' ), WPB_VC_REQUIRED_VERSION, true );
			}
		}

		/**
		 * @param string $type
		 * @return bool
		 */
		protected function isSamePostType( $type = '' ) {
			if ( empty( $type ) ) {
				$type = $this->getPostType();
			}

			return self::postType() === $type;
		}
	}
}
