$('body').vegas({
	color: "black",
	timer: false,
	delay: 15000,
	transitionDuration: 5000,
	transition: "fade2",
	overlay: "/js/vendor/vegas/overlays/02.png",
  slides: [
	  { src: '/img/bg/1.jpg' },
	  { src: '/img/bg/2.jpg' },
	  { src: '/img/bg/3.jpg' }
  ],
});

var activateMenu = function() { 
	$('#cssmenu li.has-sub>a').on('click', function(){
		$(this).removeAttr('href');
		var element = $(this).parent('li');
		if (element.hasClass('open')) {
			element.removeClass('open');
			element.find('li').removeClass('open');
			element.find('ul').slideUp();
		}
		else {
			element.addClass('open');
			element.children('ul').slideDown();
			element.siblings('li').children('ul').slideUp();
			element.siblings('li').removeClass('open');
			element.siblings('li').find('li').removeClass('open');
			element.siblings('li').find('ul').slideUp();
		}
	});
}

var ajaxError = function() {

}

var ajaxCompleted = function(data, textStatus, jqXHR) {
	data = data.replace(/<(\/?)(head|html|body)(?=\s|>)/g, '<foo$1$2');
  $response = $(data);
	var newContent = $response.find("#content");

	$("#content").replaceWith(newContent);
}

var linkHandler = function(e) {
	e.preventDefault();

	$(".active-menu").removeClass("active-menu");

	var $clicked = $(e.target);
	if ($clicked.prop("tagName") != "SPAN") {
		$clicked = $clicked.find("span");
	};
	$clicked.addClass("active-menu");

	var content = $("#content");
	content.children().hide();

	content.show();

	var page = $($(e.currentTarget).attr("open-div"));
	page.show();
}

var pageLoaded = function() {
	activateMenu();
	$(document).on("click", '#cssmenu li:not(.has-sub) a', linkHandler.bind(this));
}

if (document.readyState !== "complete") {
	$(document).ready(pageLoaded);
} else {
	pageLoaded();
}