import MatrixOptical from './components/matrix-optical';

import 'aos/dist/aos.css';
import AOS from 'aos';

Alpine.data('matrixOptical', MatrixOptical);

AOS.init({
    duration: 1000, // Duración de la animación en milisegundos
    once: true,     // Las animaciones solo deben ejecutarse una vez al desplazarse hacia abajo
});
