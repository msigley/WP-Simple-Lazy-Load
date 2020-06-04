(function() {
	function load_image( image ) {
		if( image.complete )
			return;

		image.loading = 'eager';
		if( image.src )
			image.src = image.src;
		if( image.srcset )
			image.srcset = image.srcset;
	}

	function image_needs_loaded( image ) {
		if( image.offsetParent === null )
			return false;
		if ( image.complete )
				return false;
		return true;
	}

	var loadAllImagesRunning = false;
	async function load_all_images_in_view() {
		if( loadAllImagesRunning )
			return;

		loadAllImagesRunning = true;
		var imagesNotLoaded = [ ...document.querySelectorAll( 'img' ) ].filter( function( image ) {
			return image_needs_loaded( image );
		} );

		for( let image of imagesNotLoaded )
			load_image( image );

		if( imagesNotLoaded.length ) {
			if( imagesNotLoadedPrintText.parentElement == null )
				document.body.insertBefore( imagesNotLoadedPrintText, document.body.childNodes[0] );
			
			await new Promise( function( resolve, reject ) {
				( function timer( i = 0 ) {
					i++;
					if( i > 120 ) {
						reject( new Error( 'Failed to load all images in under 30 seconds.' ) );
						return;
					}
					setTimeout( function() {
						imagesNotLoaded = imagesNotLoaded.filter( function( image ) {
							return image_needs_loaded( image );
						} );
						
						console.log( imagesNotLoaded );
						if( imagesNotLoaded.length ) {
							timer( i );
							return;
						}

						resolve();
					}, 250 );
				} )();
			} );
		}
		
		imagesNotLoadedPrintText.remove();
		loadAllImagesRunning = false;
	}

	// Print Image Handling
	var imagesNotLoadedPrintText = document.createElement( 'h1' ),
		printCss = document.createElement( 'style' );
	
	imagesNotLoadedPrintText.appendChild( document.createTextNode( 'Images not loaded yet. Please close and reopen your print window.' ) );
	imagesNotLoadedPrintText.setAttribute( 'style', 'display: none;' );
	imagesNotLoadedPrintText.classList.add( 'lazy-load-print-only' );

	printCss.appendChild( document.createTextNode( '.lazy-load-print-only { display: none; }' ) );
	printCss.setAttribute( 'media', 'print' );
	document.body.appendChild( printCss );


	var printStyleSheets = null;
	function beforeprint() {
		printStyleSheets = [];
		for( let styleSheet of document.styleSheets ) {
			var media = [ ...styleSheet.media ];
			if( media.indexOf( 'print' ) === -1 && media.indexOf( 'all' ) !== -1 )
				continue;

			styleSheet.media.appendMedium( 'all' );
			printStyleSheets.push( styleSheet );
		}

		load_all_images_in_view();
	}

	function afterprint() {
		if( null === printStyleSheets || printStyleSheets.length < 1 )
			return;

		for( let styleSheet of printStyleSheets ) {
			styleSheet.media.deleteMedium( 'all' );
		}
		printStyleSheets = null;
	}

	if( 'onbeforeprint' in window ) {
		window.addEventListener( 'beforeprint', beforeprint );
		window.addEventListener( 'afterprint', afterprint );
	} else if( window.matchMedia ) {
		window.matchMedia( 'print' ).addListener( function( mql ) {
			if( mql.matches )
				beforeprint();
			else
				afterprint();
		} );
	}
})();