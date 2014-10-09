jQuery(function($) {
	var body_height = $(window).outerHeight();
	var window_scroll;
	var tweet_post_box = $('#tweet-post-box-prompt');

	$(window).scroll(function() {
		window_scroll = $(window).scrollTop();

		if ( window_scroll > body_height / 2 ) {
			tweet_post_box.not( '.closed' ).fadeIn();
		}
	});

	$('.tweet-post-box-prompt-close').click(function() {
		tweet_post_box.animate({'bottom': -50, 'opacity': 0}, 300);
		setTimeout(function() {
			tweet_post_box.hide().addClass('closed');
		}, 300);
	});
});

function tweet_post_box_prompt_open_win( url ) {
	window.open(url,'tweetwindow','width=550,height=450,location=yes,directories=no,channelmode=no,menubar=no,resizable=no,scrollbars=no,status=no,toolbar=no');
	return false;
}