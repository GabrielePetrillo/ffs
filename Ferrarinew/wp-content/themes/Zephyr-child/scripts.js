document.addEventListener("DOMContentLoaded", function () {
  const altMenu = document.querySelector(".alternative-menu");
  if (!altMenu) return; // esci se non c'è alternative-menu

  altMenu.querySelectorAll('.alternative-menu a[href^="#"]').forEach(anchor => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();

      const targetID = this.getAttribute("href").substring(1);
      const targetEl = document.getElementById(targetID);

      if (targetEl) {
        const offset = window.innerHeight * 0.2; // 10% altezza finestra
        const elementPosition = targetEl.getBoundingClientRect().top + window.pageYOffset;
        const scrollToPosition = elementPosition - offset;

        window.scrollTo({
          top: scrollToPosition,
          behavior: "smooth",
        });
      }
    });
  });
});

document.addEventListener("DOMContentLoaded", function () {
    const altMenu = document.querySelector(".alternative-menu");

    if (!altMenu) return;

    const menuInitialTop = altMenu.getBoundingClientRect().top + window.pageYOffset;
    const triggerPoint = menuInitialTop - 80;

    // Funzione che verifica e aggiunge/rimuove sticky
    function checkSticky() {
        if (window.pageYOffset >= triggerPoint) {
            altMenu.classList.add("sticky");
        } else {
            altMenu.classList.remove("sticky");
        }
    }

    // Eseguo subito al load
    checkSticky();

    // Eseguo al scroll
    window.addEventListener("scroll", checkSticky);
});

//   document.addEventListener('DOMContentLoaded', function () {
//     const menuButton = document.getElementById('hbMenu');
//     const menuContainer = document.getElementById('menuContainer');

//     function toggleMenu() {
//       const isActive = menuButton.classList.toggle('active');
//       menuContainer.classList.toggle('active');
//       menuButton.setAttribute('aria-expanded', isActive);
//     }

//     menuButton.addEventListener('click', toggleMenu);
//     menuButton.addEventListener('keydown', function (e) {
//       if (e.key === 'Enter' || e.key === ' ') {
//         e.preventDefault();
//         toggleMenu();
//       }
//     });
//   });





//   document.addEventListener("DOMContentLoaded", function () {
//   const menuLinks = document.querySelectorAll(".menu-item-has-children > a");

//   menuLinks.forEach(link => {
//     link.addEventListener("click", function (e) {
//       e.preventDefault(); // blocca il link

//       const li = this.parentElement;
//       const isOpen = li.classList.contains("open");

//       // Chiudi i fratelli aperti
//       const siblings = li.parentElement.querySelectorAll(".menu-item-has-children.open");
//       siblings.forEach(sib => {
//         if (sib !== li) sib.classList.remove("open");
//       });

//       // Toggle della classe open sul cliccato
//       li.classList.toggle("open", !isOpen);
//     });
//   });
// });


document.addEventListener('DOMContentLoaded', function () {
  const menuButton = document.getElementById('hbMenu');
  const menuContainer = document.getElementById('menuContainer');

  function closeAllSubmenus() {
    const openItems = document.querySelectorAll('.menu-item-has-children.open');
    openItems.forEach(item => item.classList.remove('open'));
  }

  function closeMenu() {
    menuButton.classList.remove('active');
    menuContainer.classList.remove('active');
    menuButton.setAttribute('aria-expanded', 'false');
    closeAllSubmenus();
  }

  function toggleMenu() {
    const isActive = menuButton.classList.toggle('active');
    menuContainer.classList.toggle('active');
    menuButton.setAttribute('aria-expanded', isActive);
    if (!isActive) closeAllSubmenus();
  }

  menuButton.addEventListener('click', toggleMenu);

  menuButton.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      toggleMenu();
    }
  });

  // Clic fuori dal menu chiude tutto
document.addEventListener('click', function (e) {
  const clickedInsideMenu = e.target.closest('nav.main-navigation a');
  const clickedOnToggle = menuButton.contains(e.target);

  // Se NON ha cliccato su un <li> del menu e nemmeno sul bottone
  if (!clickedInsideMenu && !clickedOnToggle && menuContainer.classList.contains('active')) {
    closeMenu();
  }
});


  // Gestione sottomenù
  const menuLinks = document.querySelectorAll(".menu-item-has-children > a");

  menuLinks.forEach(link => {
    link.addEventListener("click", function (e) {
      e.preventDefault();
      const li = this.parentElement;
      const isOpen = li.classList.contains("open");

      // Chiude gli altri sottomenù
      const siblings = li.parentElement.querySelectorAll(".menu-item-has-children.open");
      siblings.forEach(sib => {
        if (sib !== li) sib.classList.remove("open");
      });

      li.classList.toggle("open", !isOpen);
    });
  });
});