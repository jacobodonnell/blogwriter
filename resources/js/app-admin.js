import Alpine from './alpine-setup';
import './utilities';
import sidebar from './components/sidebar';
import customizerLayout from './components/customizer-layout';
import articleCustomizer from './components/article-customizer';
import featuredImage from './components/featured-image';
import uploadPhotoModal from './components/upload-photo-modal';

Alpine.data('sidebar', sidebar);
Alpine.data('customizerLayout', customizerLayout);
Alpine.data('articleCustomizer', articleCustomizer);
Alpine.data('featuredImage', featuredImage);
Alpine.data('uploadPhotoModal', uploadPhotoModal);

Alpine.start();
