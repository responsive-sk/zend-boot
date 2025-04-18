// Load jQuery and Bootstrap
// Import our custom CSS
// import '../scss/index.scss'
import 'bootstrap/dist/js/bootstrap.bundle';

// Import all of Bootstrap's JS

try {
    window.$ = window.jQuery = require('jquery');
    require('bootstrap');
    require('bootstrap-fileinput');
    require('bootstrap-slider');
    require('jquery-mousewheel');

    window.toastr = require('toastr');
} catch (e) {
}
