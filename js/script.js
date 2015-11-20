$(function() {
	var input = $('#text-input');
	var preview = $('#preview');
	
	var cm = CodeMirror.fromTextArea(input.get(0), {
		lineNumbers: true,
		mode: "markdown",
		lineWrapping: $('#line-wrapping').prop('checked')
	});
	
	var scrollsync = $('#scroll-sync');
	var enablePreview = $('#enable-preview');
	
	cm.on('change', function() {
		if (enablePreview.prop('checked')) {
			cm.save();
			preview.html(Markdown(input.val()).replace(/<table>/g, '<table class="table">'));
		}
	});
	
	cm.on('scroll', function() {
		if (scrollsync.prop('checked')) {
			preview.scrollTop(cm.getScrollInfo().top);
		}
	});
	
	$('#line-wrapping').change(function() {
		cm.setOption('lineWrapping', $('#line-wrapping').prop('checked'));
	});
	
	function updateEnablePreview() {
		if (!enablePreview.prop('checked')) {
			$('#editor-outer').css('width', '100%');
			$('#editor-outer').css('padding-right', '0');
			$('#preview-outer').hide();
		} else {
			cm.save();
			preview.html(Markdown(input.val()).replace(/<table>/g, '<table class="table">'));
			$('#editor-outer').css('width', '50%');
			$('#editor-outer').css('padding-right', '4px');
			$('#preview-outer').show();
		}
	}
	
	enablePreview.change(updateEnablePreview);
	updateEnablePreview();
	
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
	
	function getSelectionLines() {
		var lines = [];
		var selections = cm.listSelections();
		for (var j = 0; j < selections.length; j++) {
			var s = selections[j];
			var line1 = s.anchor.line;
			var line2 = s.head.line;
			if (line1 > line2) {
				var t = line1;
				line1 = line2;
				line2 = t;
			}
			for (var i = line1; i <= line2; i++) {
				lines.push(i);
			}
		}
		return lines;
	}
	
	$('#quote').click(function() {
		var lines = getSelectionLines();
		for (var i = 0; i < lines.length; i++) {
			var line = lines[i];
			var oldText = cm.getLine(line);
			var replacement = oldText[0] == '>' ? '>' : '> ';
			cm.replaceRange(replacement, {line:line, ch:0}, {line:line, ch:0});
		}
	});
	
	$('#dequote').click(function() {
		var lines = getSelectionLines();
		for (var i = 0; i < lines.length; i++) {
			var line = lines[i];
			var oldText = cm.getLine(line);
			var newText = oldText;
			if (oldText.length > 0 && oldText[0] == '>') {
				newText = newText.substring(1);
				if (oldText.length > 1 && oldText[1] == ' ') {
					newText = newText.substring(1);
				}
			}
			cm.replaceRange(newText, {line:line, ch:0}, {line:line, ch:oldText.length});
		}
	});
	
	function addList(desc) {
		var lines = getSelectionLines();
		var hasBase = false;
		var id = 1;
		for (var i = 0; i < lines.length; i++) {
			var line = lines[i];
			var oldText = cm.getLine(line);
			var newText = oldText;
			if (oldText.length == 0) {
				id = 1;
				continue;
			} else if (oldText.match(/^[\t]*([0-9]+\.|\*|\+|\-)[ ]/)) {
				if (hasBase) {
					newText = '\t' + oldText;
				}
			} else {
				hasBase = true;
				newText = desc(id) + oldText;
				id++;
			}
			cm.replaceRange(newText, {line:line, ch:0}, {line:line, ch:oldText.length});
		}
	}
	
	$('#ordered-list').click(function() {
		addList(function(id) { return id + '. '; });
	});
	
	$('#unordered-list').click(function() {
		addList(function(id) { return '* '; });
	});
	
	$('#remove-list').click(function() {
		var lines = getSelectionLines();
		var hasBase = false;
		for (var i = 0; i < lines.length; i++) {
			var line = lines[i];
			var oldText = cm.getLine(line);
			var newText = oldText;
			var match;
			if (match = oldText.match(/^[\t]+([0-9]+\.|\*|\+|\-)[ ]/)) {
				if (hasBase) {
					newText = oldText.substring(1);
				}
			} else if (match = oldText.match(/^([0-9]+\.|\*|\+|\-)[ ]/)) {
				hasBase = true;
				newText = oldText.substring(match[0].length);
			}
			cm.replaceRange(newText, {line:line, ch:0}, {line:line, ch:oldText.length});
		}
	});
});
