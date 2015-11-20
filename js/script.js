$(function() {
	var input = $('#text-input');
	var preview = $('#preview');
	
	var cm = CodeMirror.fromTextArea(input.get(0), {
		lineNumbers: true,
		mode: "markdown",
		lineWrapping: $('#line-wrapping').prop('checked')
	});
	
	var scrollsync = $('#scroll-sync');
	var enablepreview = $('#enable-preview');
	
	cm.on('change', function() {
		if (enablepreview.prop('checked')) {
			cm.save();
			preview.html(Markdown(input.val()).replace(/<table>/g, '<table class="table">'));
		}
	});
	
	cm.on('scroll', function() {
		if (scrollsync.prop('checked')) {
			preview.scrollTop(cm.getScrollInfo().top);
		}
	});
	
	$('#line-wrapping').click(function() {
		cm.setOption('lineWrapping', $('#line-wrapping').prop('checked'));
	});
	
	$('#config input[type=checkbox]').each(function(index, elem) {
		var self = $(elem);
		self.change(function() {
			var flag = $.cookie('flag');
			if ('undefined' === typeof flag) flag = FLAG_DEFAULT;
			var set = self.prop('checked');
			if (set) {
				flag |= (1 << index);
			} else {
				flag &= ~(1 << index);
			}
			$.cookie('flag', flag);
		});
	});
	
	var contentOuter = $('.content-outer');
	var nav = $('nav');
	
	$(window).resize(function(e) {
		contentOuter.css('padding-bottom', nav.height());
	});
	
	$(window).bind('beforeunload', function(e) {
		if (!updateToServer(false)) {
			e.preventDefault();
		}
	});
	
	setInterval(60000, updateToServer);
	
	function updateToServer(async) {
		if ('undefined' === typeof async) { async = true; }
		var success = false;
		cm.save();
		$.ajax({
			type: 'post',
			url: 'update.php',
			data: {content: input.val()},
			async: async,
			timeout: 3000,
			success: function() { success = true; }
		});
		return success;
	}
});
