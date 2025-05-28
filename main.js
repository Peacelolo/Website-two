  const teamMembersData = {
    zomzi: {
      name: "Zomzi Mpelane",
      title: "Co-founder & Head of Administration",
      description: "Zomzi brings extensive experience in administrative management and business development, ensuring the smooth operation and strategic growth of HerdTrace. Her meticulous approach and leadership are vital to our daily functions and long-term planning.",
      image: "images/zomzi.jpeg"
    },
    zusiphe: {
      name: "Zusiphe Makeleni",
      title: "Co-founder & Head of Sales and Marketing",
      description: "Zusiphe is a dynamic marketing professional with a background in digital marketing, driving HerdTrace's market presence and customer engagement. Her innovative strategies help us connect with farmers and expand our reach across communities.",
      image: "images/zusiphe.jpeg"
    },
    akhona: {
      name: "Akhona Dlala",
      title: "Co-founder & Head of Finance",
      description: "Akhona is a finance expert responsible for managing the financial health of HerdTrace, ensuring sustainability and growth. Her sharp analytical skills and foresight are crucial in navigating our financial landscape and securing resources for our mission.",
      image: "images/akhona.jpeg"
    },
    noxolo: {
      name: "Noxolo Mjokovane",
      title: "Co-founder & Chief Operations Officer",
      description: "Noxolo oversees the operations at HerdTrace, ensuring efficient service delivery and product deployment. Her leadership in operational excellence guarantees that our solutions are effectively implemented and continuously improved to meet farmer needs.",
      image: "images/noxolo.jpeg"
    }
  };

  const heroImages = document.querySelectorAll('.hero-slide-image');
  let currentHeroIndex = 0;

  function showHeroSlide(index) {
    heroImages.forEach((img, i) => {
      img.classList.remove('active');
    });
    heroImages[index].classList.add('active');
  }

  function nextHeroSlide() {
    currentHeroIndex = (currentHeroIndex + 1) % heroImages.length;
    showHeroSlide(currentHeroIndex);
  }

  if (heroImages.length > 1) {
    setInterval(nextHeroSlide, 7000);
    showHeroSlide(currentHeroIndex);
  }

  document.querySelectorAll('.faq-toggle').forEach(button => {
    button.addEventListener('click', () => {
      const faqItem = button.closest('.faq-item');
      const content = button.nextElementSibling;
      const icon = button.querySelector('svg');
      const isExpanded = button.getAttribute('aria-expanded') === 'true';

      document.querySelectorAll('.faq-item.active').forEach(activeFaqItem => {
        if (activeFaqItem !== faqItem) {
          activeFaqItem.classList.remove('active');
          const activeContent = activeFaqItem.querySelector('.faq-content');
          const activeToggle = activeFaqItem.querySelector('.faq-toggle');
          const activeIcon = activeFaqItem.querySelector('svg');

          activeContent.classList.remove('open');
          activeToggle.setAttribute('aria-expanded', 'false');
          activeIcon.classList.remove('rotate-180');
        }
      });

      if (isExpanded) {
        faqItem.classList.remove('active');
        content.classList.remove('open');
        button.setAttribute('aria-expanded', 'false');
        icon.classList.remove('rotate-180');
      } else {
        faqItem.classList.add('active');
        content.classList.add('open');
        button.setAttribute('aria-expanded', 'true');
        icon.classList.add('rotate-180');
      }
    });
  });

  const animateOnScrollElements = document.querySelectorAll('.animate-on-scroll');
  const animateStaggerElements = document.querySelectorAll('.animate-stagger');
  const sectionSeparators = document.querySelectorAll('.section-separator');

  let lastScrollY = window.scrollY;
  let hrVisibilityState = new Map();

  const generalObserverOptions = {
    root: null,
    rootMargin: '0px',
    threshold: 0.1
  };

  const generalObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      const element = entry.target;
      if (entry.isIntersecting && !element.classList.contains('section-separator')) {
        element.classList.add('animate-in');
        if (element.classList.contains('animate-stagger')) {
            Array.from(element.children).forEach((child, index) => {
                child.style.transitionDelay = `${index * 0.1}s`;
                child.style.opacity = '1';
                child.style.transform = 'translateY(0)';
            });
        }
        observer.unobserve(element);
      }
    });
  }, generalObserverOptions);

  animateOnScrollElements.forEach(element => {
    generalObserver.observe(element);
  });
  animateStaggerElements.forEach(element => {
    generalObserver.observe(element);
  });

  const hrObserverOptions = {
    root: null,
    rootMargin: '0px',
    threshold: [0, 1.0]
  };

  const hrObserver = new IntersectionObserver((entries) => {
    const currentScrollY = window.scrollY;
    const scrollDirection = currentScrollY > lastScrollY ? 'down' : 'up';
    lastScrollY = currentScrollY;

    entries.forEach(entry => {
      const element = entry.target;
      if (element.classList.contains('section-separator')) {
        const isPermanent = element.hasAttribute('data-permanent-hr');
        let currentState = hrVisibilityState.get(element) || { wasIntersecting: false, hasBeenFullyVisible: false };

        if (entry.isIntersecting) {
          element.style.transformOrigin = 'left';
          element.style.transform = 'scaleX(1)';
          element.style.opacity = '1';

          if (entry.intersectionRatio === 1.0) {
            currentState.hasBeenFullyVisible = true;
          }
          currentState.wasIntersecting = true;
        } else {
          const rect = element.getBoundingClientRect();
          const isAtPageBottom = (window.innerHeight + window.scrollY) >= (document.body.offsetHeight - 50);

          if (isPermanent && isAtPageBottom) {
            element.style.transform = 'scaleX(1)';
            element.style.opacity = '1';
          } else {
            if (scrollDirection === 'up' && rect.bottom < 0) {
              element.style.transformOrigin = 'right';
              element.style.transform = 'scaleX(0)';
              element.style.opacity = '0';
            } else if (scrollDirection === 'down' && rect.top > window.innerHeight) {
              element.style.transformOrigin = 'left';
              element.style.transform = 'scaleX(0)';
              element.style.opacity = '0';
            }
          }
          currentState.wasIntersecting = false;
        }
        hrVisibilityState.set(element, currentState);
      }
    });
  }, hrObserverOptions);

  sectionSeparators.forEach(separator => {
    hrObserver.observe(separator);
  });

  const mobileMenuButton = document.getElementById('mobile-menu-button');
  const closeMobileMenuButton = document.getElementById('close-mobile-menu');
  const mobileMenu = document.getElementById('mobile-menu');
  const firstMobileMenuItem = mobileMenu.querySelector('a');

  function openMobileMenu() {
    mobileMenu.classList.remove('hidden');
    closeMobileMenuButton.focus();
    trapFocus(mobileMenu);
  }

  function closeMobileMenu() {
    mobileMenu.classList.add('hidden');
    mobileMenuButton.focus();
  }

  mobileMenuButton.addEventListener('click', openMobileMenu);
  closeMobileMenuButton.addEventListener('click', closeMobileMenu);

  function trapFocus(element) {
    const focusableEls = element.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])');
    const firstFocusableEl = focusableEls[0];
    const lastFocusableEl = focusableEls[focusableEls.length - 1];

    element.addEventListener('keydown', function(e) {
      const isTabPressed = (e.key === 'Tab');

      if (!isTabPressed) {
        return;
      }

      if (e.shiftKey) {
        if (document.activeElement === firstFocusableEl) {
          lastFocusableEl.focus();
          e.preventDefault();
        }
      } else {
        if (document.activeElement === lastFocusableEl) {
          firstFocusableEl.focus();
          e.preventDefault();
        }
      }
    });
  }

  const contactImages = document.querySelectorAll('.contact-slide-image');
  let currentContactIndex = 0;

  function showContactSlide(index) {
    contactImages.forEach((img, i) => {
      img.classList.remove('active');
    });
    if (contactImages[index]) {
        contactImages[index].classList.add('active');
    } else {
        console.error('Attempted to show non-existent contact slide at index:', index);
    }
  }

  function nextContactSlide() {
    currentContactIndex = (currentContactIndex + 1) % contactImages.length;
    showContactSlide(currentContactIndex);
  }

  if (contactImages.length > 1) {
    setInterval(nextContactSlide, 8000);
    showContactSlide(currentContactIndex);
  } else {
    if (contactImages.length === 1) {
        contactImages[0].classList.add('active');
    }
  }

  const teamMemberCards = document.querySelectorAll('.team-member-card');
  const modalOverlay = document.getElementById('team-member-modal-overlay');
  const modalCloseButton = document.getElementById('modal-close-button');
  const modalMemberImage = document.getElementById('modal-member-image');
  const modalMemberName = document.getElementById('modal-member-name');
  const modalMemberTitle = document.getElementById('modal-member-title');
  const modalMemberDescription = document.getElementById('modal-member-description');

  function openModal(memberData) {
    modalMemberImage.src = memberData.image;
    modalMemberName.textContent = memberData.name;
    modalMemberTitle.textContent = memberData.title;
    modalMemberDescription.textContent = memberData.description;
    modalOverlay.classList.add('open');
    modalCloseButton.focus();
    trapFocus(modalOverlay);
  }

  function closeModal() {
    modalOverlay.classList.remove('open');
  }

  teamMemberCards.forEach(card => {
    card.addEventListener('click', () => {
      const memberId = card.dataset.memberId;
      const memberData = teamMembersData[memberId];
      if (memberData) {
        openModal(memberData);
      }
    });
  });

  modalCloseButton.addEventListener('click', closeModal);

  modalOverlay.addEventListener('click', (event) => {
    if (event.target === modalOverlay) {
      closeModal();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && modalOverlay.classList.contains('open')) {
      closeModal();
    }
  });

  const contactForm = document.getElementById('contact-form');
  contactForm.addEventListener('submit', function(event) {
    event.preventDefault();

    const name = document.getElementById('contact-name').value.trim();
    const surname = document.getElementById('contact-surname').value.trim();
    const phone = document.getElementById('contact-phone').value.trim();
    const email = document.getElementById('contact-email').value.trim();

    let isValid = true;

    if (name === '') {
      alert('Please enter your Name.');
      isValid = false;
    } else if (surname === '') {
      alert('Please enter your Surname.');
      isValid = false;
    } else if (phone === '' || !/^\+?[0-9\s-()]{7,20}$/.test(phone)) {
      alert('Please enter a valid Phone Number.');
      isValid = false;
    } else if (email === '' || !/\S+@\S+\.\S+/.test(email)) {
      alert('Please enter a valid Email Address.');
      isValid = false;
    }

    if (isValid) {
      console.log('Form submitted:', { name, surname, phone, email });
      alert('Thank you for joining our waiting list! We will be in touch soon.');
      contactForm.reset();
    }
  });


  document.addEventListener('DOMContentLoaded', () => {
    sectionSeparators.forEach(hr => {
      hr.style.transform = 'scaleX(0)';
      hr.style.opacity = '0';
    });
  });

  const heroMessages = [
    "Get started with HerdTrace and secure your livestock.",
    "Livestock Tracking Made Easy",
    "Ensure the safety of your herd at all times."
  ];

  const messageSlider = document.getElementById("hero-message-slider");
  const messageElement = messageSlider.querySelector("p");
  let messageIndex = 0;

  setInterval(() => {
    messageElement.classList.remove("opacity-100");
    messageElement.classList.add("opacity-0");

    setTimeout(() => {
      messageIndex = (messageIndex + 1) % heroMessages.length;
      messageElement.textContent = heroMessages[messageIndex];
      messageElement.classList.remove("opacity-0");
      messageElement.classList.add("opacity-100");
    }, 500);
  }, 4000); // 4 seconds
</script>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
  const faqSwiper = new Swiper(".faqSwiper", {
    loop: true,
    slidesPerView: 1,
    spaceBetween: 30,
    autoplay: {
      delay: 5000,
      disableOnInteraction: false,
    },
    navigation: {
      nextEl: ".swiper-button-next",
      prevEl: ".swiper-button-prev",
    },
    breakpoints: {
      768: {
        slidesPerView: 1,
        spaceBetween: 30,
      }
    }
  });