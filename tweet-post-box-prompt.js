jQuery(function($) {
	var body_height = $(window).outerHeight();
	var window_scroll;

	$(window).scroll(function() {
		window_scroll = $(window).scrollTop();

		if ( window_scroll > body_height / 2 ) {
			$('#tweet-post-box-prompt').fadeIn();
		}
		else {
			$('#tweet-post-box-prompt').fadeOut();
		}
	});
});

function tweet_post_box_prompt_open_win( url ) {
	window.open(url,'tweetwindow','width=550,height=450,location=yes,directories=no,channelmode=no,menubar=no,resizable=no,scrollbars=no,status=no,toolbar=no');
	return false;
}