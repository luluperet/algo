/**
 * jTinder initialization
 */
$("#tinderslide").jTinder({
	// dislike callback
    onDislike: function (item) {
	    // set the status text
        $('#status').html('Dislike image ' + (item.index()+1));
    },
	// like callback
    onLike: function (item) {
	    // set the status text
        $('#status').html('Like image ' + (item.index()+1));
    },
    onSeen: function (item) {
	    // set the status text
        $('#status').html('Seen image ' + (item.index()+1));
    },
	animationRevertSpeed: 200,
	animationSpeed: 400,
	threshold: 1,
	likeSelector: '.like',
	dislikeSelector: '.dislike',
		seenSelector: '.seen'

});

/**
 * Set button action to trigger jTinder like & dislike.
 */
$('.actions .like, .actions .dislike, .actions .seen').click(function(e){
	e.preventDefault();
	$("#tinderslide").jTinder($(this).attr('class'));
});