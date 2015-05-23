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
	    ]
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

		if (document.readyState !== "complete") {
			$(document).ready(activateMenu);
		} else {
			activateMenu();
		}