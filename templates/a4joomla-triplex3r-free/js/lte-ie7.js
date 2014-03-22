/* Use this script if you need to support IE 7 and IE 6. */

window.onload = function() {
	function addIcon(el, entity) {
		var html = el.innerHTML;
		el.innerHTML = '<span style="font-family: \'icomoon2\'">' + entity + '</span>' + html;
	}
	var icons = {
			'icon2-google' : '&#xf000;',
			'icon2-google-plus' : '&#xf001;',
			'icon2-google-plus-2' : '&#xf002;',
			'icon2-google-plus-3' : '&#xf003;',
			'icon2-google-plus-4' : '&#xf004;',
			'icon2-google-drive' : '&#xf005;',
			'icon2-facebook' : '&#xf006;',
			'icon2-facebook-2' : '&#xf007;',
			'icon2-facebook-3' : '&#xf008;',
			'icon2-instagram' : '&#xf009;',
			'icon2-twitter' : '&#xf00a;',
			'icon2-twitter-2' : '&#xf00b;',
			'icon2-twitter-3' : '&#xf00c;',
			'icon2-feed' : '&#xf00d;',
			'icon2-feed-2' : '&#xf00e;',
			'icon2-feed-3' : '&#xf00f;',
			'icon2-youtube' : '&#xf010;',
			'icon2-youtube-2' : '&#xf011;',
			'icon2-vimeo' : '&#xf012;',
			'icon2-vimeo2' : '&#xf013;',
			'icon2-vimeo-2' : '&#xf014;',
			'icon2-lanyrd' : '&#xf015;',
			'icon2-flickr' : '&#xf016;',
			'icon2-flickr-2' : '&#xf017;',
			'icon2-flickr-3' : '&#xf018;',
			'icon2-flickr-4' : '&#xf019;',
			'icon2-picassa' : '&#xf01a;',
			'icon2-picassa-2' : '&#xf01b;',
			'icon2-dribbble' : '&#xf01c;',
			'icon2-dribbble-2' : '&#xf01d;',
			'icon2-dribbble-3' : '&#xf01e;',
			'icon2-forrst' : '&#xf01f;',
			'icon2-forrst-2' : '&#xf020;',
			'icon2-deviantart' : '&#xf021;',
			'icon2-deviantart-2' : '&#xf022;',
			'icon2-steam' : '&#xf023;',
			'icon2-steam-2' : '&#xf024;',
			'icon2-github' : '&#xf025;',
			'icon2-github-2' : '&#xf026;',
			'icon2-github-3' : '&#xf027;',
			'icon2-github-4' : '&#xf028;',
			'icon2-github-5' : '&#xf029;',
			'icon2-wordpress' : '&#xf02a;',
			'icon2-wordpress-2' : '&#xf02b;',
			'icon2-joomla' : '&#xf02c;',
			'icon2-blogger' : '&#xf02d;',
			'icon2-blogger-2' : '&#xf02e;',
			'icon2-tumblr' : '&#xf02f;',
			'icon2-tumblr-2' : '&#xf030;',
			'icon2-yahoo' : '&#xf031;',
			'icon2-tux' : '&#xf032;',
			'icon2-apple' : '&#xf033;',
			'icon2-finder' : '&#xf034;',
			'icon2-android' : '&#xf035;',
			'icon2-windows' : '&#xf036;',
			'icon2-windows8' : '&#xf037;',
			'icon2-soundcloud' : '&#xf038;',
			'icon2-soundcloud-2' : '&#xf039;',
			'icon2-skype' : '&#xf03a;',
			'icon2-reddit' : '&#xf03b;',
			'icon2-linkedin' : '&#xf03c;',
			'icon2-lastfm' : '&#xf03d;',
			'icon2-lastfm-2' : '&#xf03e;',
			'icon2-delicious' : '&#xf03f;',
			'icon2-stumbleupon' : '&#xf040;',
			'icon2-stumbleupon-2' : '&#xf041;'
		},
		els = document.getElementsByTagName('*'),
		i, attr, html, c, el;
	for (i = 0; i < els.length; i += 1) {
		el = els[i];
		attr = el.getAttribute('data-icon2');
		if (attr) {
			addIcon(el, attr);
		}
		c = el.className;
		c = c.match(/icon2-[^\s'"]+/);
		if (c && icons[c[0]]) {
			addIcon(el, icons[c[0]]);
		}
	}
};