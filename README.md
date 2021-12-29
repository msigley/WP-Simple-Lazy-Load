[![ko-fi](https://www.ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/A0A01FORH)
# WP-Simple-Lazy-Load
Simple native image and iframe lazy loading implementation for Wordpress.

Filters the output between the 'wp_head' and 'wp_footer' actions and adds ```loading="lazy"``` to any ```<img>``` or ```<iframe>``` tag that doesn't have a loading attribute defined. A gray placeholder background is also implemented for images or iframes that haven't loaded yet.

Includes javascript that solves common printing issues caused by lazy loaded images.
