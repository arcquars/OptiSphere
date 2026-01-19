<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="CERISIER - Importadora de Lentes Oftalmicos Especiales">
  <title>CERISIER - Lentes Oftálmicos Especiales</title>
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
    rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/home.css') }}">

</head>

<body>
  <!-- NavBar -->
  <nav class="navbar" id="navbar">
    <div class="navbar-container">
      <div class="navbar-logo">
        <img src="{{ asset('img/logo.png') }}" alt="CERISIER" class="logo-img" />
      </div>

      <div class="navbar-menu" id="navbar-menu">
        <a href="#nosotros" class="nav-link">Nosotros</a>
        <a href="#productos" class="nav-link">Productos</a>
        <a href="#distribucion" class="nav-link">Distribución</a>
        <a href="#clientes" class="nav-link">Clientes</a>
        <a href="{{ route('login') }}" class="nav-link">Administración</a>
        <a href="#contacto" class="nav-link nav-btn">Contáctanos</a>
      </div>

      <button class="menu-toggle" id="menu-toggle" aria-label="Toggle menu">
        <span class="hamburger-line top"></span>
        <span class="hamburger-line middle"></span>
        <span class="hamburger-line bottom"></span>
      </button>
    </div>
  </nav>

  <!-- Home Content -->
  <div class="home">
    <!-- Hero Section -->
    <section class="hero-section">
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <h1 class="hero-title">Presición óptica</h1>
        <h2 class="hero-subtitle">Que transforma la</h2>
        <h3 class="hero-vision">Visión</h3>
        <a href="#productos" class="hero-cta">Ver catálogo</a>
      </div>
    </section>

    <!-- Nosotros Section -->
    <section id="nosotros" class="section section-nosotros">
      <div class="container">
        <h2 class="section-title">Nosotros</h2>
        <div class="section-divider"></div>
        <div class="content-grid">
          <div class="content-text">
            <h3>Líderes en Importación de Lentes Oftálmicos Especiales</h3>
            <p>
              En CERISIER, nos especializamos en la importación de material oftálmico de la más alta calidad.
              Nuestra misión es proporcionar soluciones visuales innovadoras que mejoren la calidad de vida de nuestros
              clientes.
            </p>
            <p>
              Trabajamos con las mejores marcas internacionales para traer a Bolivia tecnología de punta en lentes
              oftálmicos especiales,
              garantizando productos que cumplen con los más altos estándares de calidad.
            </p>
            <div class="features">
              <div class="feature-item">
                <svg class="feature-icon" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
                </svg>
                <span>Productos Premium</span>
              </div>
              <div class="feature-item">
                <svg class="feature-icon" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
                </svg>
                <span>Tecnología Avanzada</span>
              </div>
              <div class="feature-item">
                <svg class="feature-icon" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
                </svg>
                <span>Garantía de Calidad</span>
              </div>
            </div>
          </div>
          <div class="stats-grid">
            <div class="stat-card stat-red">
              <div class="stat-value">15+</div>
              <div class="stat-label">Años de Experiencia</div>
            </div>
            <div class="stat-card stat-purple">
              <div class="stat-value">100%</div>
              <div class="stat-label">Calidad Garantizada</div>
            </div>
            <div class="stat-card stat-blue">
              <div class="stat-value">500+</div>
              <div class="stat-label">Clientes Satisfechos</div>
            </div>
            <div class="stat-card stat-dark">
              <div class="stat-value">24/7</div>
              <div class="stat-label">Soporte Técnico</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Productos Section -->
    <section id="productos" class="section section-productos">
      <div class="container">
        <h2 class="section-title">Nuestros Productos</h2>
        <div class="section-divider"></div>
        <p class="section-description">Tecnología de vanguardia para el cuidado de tu visión</p>

        <div class="products-grid">
          <div class="product-card">
            <div class="product-header product-blue">
              <svg class="product-icon" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                <path fill-rule="evenodd"
                  d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                  clip-rule="evenodd" />
              </svg>
              <h3>BLUE BLOCKER</h3>
            </div>
            <div class="product-body">
              <h4>Protección contra la Luz Azul</h4>
              <p>Lentes especializados que filtran la luz azul dañina emitida por pantallas digitales, reduciendo la
                fatiga visual y mejorando tu bienestar.</p>
              <ul class="product-features">
                <li>Reduce fatiga ocular</li>
                <li>Mejora calidad del sueño</li>
                <li>Protección UV completa</li>
              </ul>
              <a href="#contacto" class="product-btn">Más información</a>
            </div>
          </div>

          <div class="product-card">
            <div class="product-header product-red">
              <svg class="product-icon" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                  clip-rule="evenodd" />
              </svg>
              <h3>PROGRESIVOS</h3>
            </div>
            <div class="product-body">
              <h4>Lentes Progresivos Premium</h4>
              <p>Tecnología multifocal avanzada que proporciona visión clara a todas las distancias sin líneas visibles.
              </p>
              <ul class="product-features">
                <li>Visión nítida a todas las distancias</li>
                <li>Adaptación rápida y cómoda</li>
                <li>Diseño estético sin líneas</li>
              </ul>
              <a href="#contacto" class="product-btn">Más información</a>
            </div>
          </div>

          <div class="product-card">
            <div class="product-header product-dark">
              <svg class="product-icon" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                  d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z"
                  clip-rule="evenodd" />
              </svg>
              <h3>ANTIRREFLEJOS</h3>
            </div>
            <div class="product-body">
              <h4>Lentes Antirreflejantes</h4>
              <p>Tratamiento especializado que elimina reflejos molestos y mejora la transmisión de luz.</p>
              <ul class="product-features">
                <li>Elimina reflejos molestos</li>
                <li>Mayor transmisión de luz</li>
                <li>Fácil limpieza y mantenimiento</li>
              </ul>
              <a href="#contacto" class="product-btn">Más información</a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Distribución Section -->
    <section id="distribucion" class="section section-white">
      <div class="container">
        <h2 class="section-title">Distribución</h2>
        <div class="section-divider"></div>
        <p class="section-description">Cobertura nacional con la mejor logística</p>
        <div class="distribution-content">
          <p>Contamos con una red de distribución que cubre todo el territorio boliviano, garantizando que nuestros
            productos lleguen a tiempo y en perfectas condiciones.</p>
          <div class="benefits-grid">
            <div class="benefit-card">
              <div class="benefit-icon">🚚</div>
              <h4>Envío Rápido</h4>
              <p>Entrega en 24-48 horas</p>
            </div>
            <div class="benefit-card">
              <div class="benefit-icon">📦</div>
              <h4>Empaque Seguro</h4>
              <p>Productos protegidos</p>
            </div>
            <div class="benefit-card">
              <div class="benefit-icon">🌍</div>
              <h4>Cobertura Nacional</h4>
              <p>Llegamos a todo Bolivia</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Clientes Section -->
    <section id="clientes" class="section section-light">
      <div class="container">
        <h2 class="section-title">Nuestros Clientes</h2>
        <div class="section-divider"></div>
        <p class="section-description">Confían en nuestra calidad y servicio</p>
        <div class="clients-content">
          <p>Trabajamos con las principales ópticas y profesionales de la salud visual en Bolivia, brindando productos y
            servicios que superan sus expectativas.</p>
          <div class="testimonial">
            <p class="testimonial-text">"La calidad de los productos CERISIER es excepcional. Nuestros clientes están
              muy satisfechos con los resultados."</p>
            <p class="testimonial-author">— Cliente Satisfecho</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Contacto Section -->
    <section id="contacto" class="section section-contact">
      <div class="container">
        <h2 class="section-title">Contáctanos</h2>
        <div class="section-divider"></div>
        <p class="section-description">Estamos aquí para ayudarte</p>

        <div class="contact-grid">
          <div class="contact-form">
            <form id="contactForm">
              <div class="form-group">
                <label for="name">Nombre Completo</label>
                <input type="text" id="name" required />
              </div>
              <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" required />
              </div>
              <div class="form-group">
                <label for="phone">Teléfono</label>
                <input type="tel" id="phone" />
              </div>
              <div class="form-group">
                <label for="message">Mensaje</label>
                <textarea id="message" rows="4" required></textarea>
              </div>
              <button type="submit" class="submit-btn">Enviar Mensaje</button>
              <div id="successMessage" class="success-message" style="display: none;">
                ¡Gracias por tu mensaje! Te contactaremos pronto.
              </div>
            </form>
          </div>

          <div class="contact-info">
            <div class="info-card">
              <h4>📍 Ubicación</h4>
              <p>Bolivia</p>
            </div>
            <div class="info-card">
              <h4>📞 Teléfono</h4>
              <p>+591 [Tu Número]</p>
              <p>WhatsApp: +591 [Tu WhatsApp]</p>
            </div>
            <div class="info-card">
              <h4>✉️ Email</h4>
              <p>info@cerisier.com</p>
              <p>ventas@cerisier.com</p>
            </div>
            <div class="info-card">
              <h4>🕐 Horarios</h4>
              <p>Lunes - Viernes: 9:00 - 18:00</p>
              <p>Sábados: 9:00 - 13:00</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
      <div class="container">
        <p>&copy; 2026 CERISIER - Lentes Oftálmicos Especiales. Todos los derechos reservados.</p>
      </div>
    </footer>
  </div>

  <script>

    document.addEventListener('DOMContentLoaded', () => {
    // Navigation Scroll Effect
    const navbar = document.getElementById('navbar');

    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('navbar-scrolled');
        } else {
            navbar.classList.remove('navbar-scrolled');
        }
    });

    // Mobile Menu Toggle
    const menuToggle = document.getElementById('menu-toggle');
    const navbarMenu = document.getElementById('navbar-menu');
    const navLinks = document.querySelectorAll('.nav-link');

    menuToggle.addEventListener('click', () => {
        navbarMenu.classList.toggle('active');
        menuToggle.classList.toggle('active');
    });

    // Close menu when clicking a link
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            navbarMenu.classList.remove('active');
            menuToggle.classList.remove('active');
        });
    });

    // Contact Form Submission
    const contactForm = document.getElementById('contactForm');
    const successMessage = document.getElementById('successMessage');

    if (contactForm) {
        contactForm.addEventListener('submit', (e) => {
            e.preventDefault();

            // Show success message
            successMessage.style.display = 'block';

            // Reset form
            contactForm.reset();

            // Hide message after 3 seconds
            setTimeout(() => {
                successMessage.style.display = 'none';
            }, 3000);
        });
    }

    // Smooth Scrolling for Anchor Links (handled by CSS scroll-behavior usually, but good to force)
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;

            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
});

  </script>
</body>

</html>