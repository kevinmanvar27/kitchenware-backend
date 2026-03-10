import './bootstrap';
import jQuery from 'jquery';

// Make jQuery available globally before importing common.js
window.$ = window.jQuery = jQuery;

// Now import common.js which depends on jQuery
import './common';