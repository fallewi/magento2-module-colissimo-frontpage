console.log('local script loaded !!!!!!!!!');

jQuery.extend(jQuery.fn, {
	frameColissimoOpen: function(params)
	{
		//On recherche l'url du serveur

		var colissimo = jQuery.noConflict();
		//var colissimo = $;

		var url = 'https://ws.colissimo.fr/widget-point-retrait/resources/js/jquery.frameColiposte.js';
		// var url = colissimo('script[src*="jquery.plugin.colissimo"]').attr("src");
		//console.log('url source = ' + url);

		var indexpath = url.indexOf('widget-point-retrait',0);
		var urlColiposte = url.substring(0, indexpath-1);
		//console.log('url coli = ' + urlColiposte);


		var lang = params.ceLang;
		var callBackFrame = params.callBackFrame;


		//ContrÃ´le des parametres
		var codeRetour = 0;
		if(params.ceCountryList == null || params.ceCountryList == '')
		{
			codeRetour = 10;
		}
		if(params.ceCountry == null || params.ceCountry == '')
		{
			codeRetour = 20;
		}
		if(params.ceLang == null || params.ceLang == '')
		{
			codeRetour = 30;
		}
		if(params.dyPreparationTime == null || params.dyPreparationTime == '')
		{
			codeRetour = 40;
		}

		var bootstrap_css_link = colissimo("<link>", {
			rel: "stylesheet",
			type: "text/css",
			href: urlColiposte + "/widget-point-retrait/resources/css/bootstrap.min.css"
		});
		bootstrap_css_link.appendTo('head');
		colissimo("head").append('\n');

		var css_link = colissimo("<link>", {
			rel: "stylesheet",
			type: "text/css",
			href: urlColiposte + "/widget-point-retrait/resources/css/mystyle.css"
		});
		css_link.appendTo('head');
		colissimo("head").append('\n');

		var ui_css_link = colissimo("<link>", {
			rel: "stylesheet",
			type: "text/css",
			href: urlColiposte + "/widget-point-retrait/resources/css/jquery-ui.min-1.11.4.css"
		});
		ui_css_link.appendTo('head');
		colissimo("head").append('\n');

		var s = document.createElement("script");
		s.type = "text/javascript";
		s.src = urlColiposte + "/widget-point-retrait/resources/js/bootstrap.min.js";
		s.defer = true;
		colissimo("head").append(s);
		colissimo("head").append('\n');

		// var sUI = document.createElement("script");
		// sUI.type = "text/javascript";
		// sUI.src = urlColiposte + "/widget-point-retrait/resources/js/jquery-ui.min-1.11.4.js";
		// colissimo("head").append(sUI);
		// colissimo("head").append('\n');


		//console.log('in integre les scripts pour la map');
		//console.log('map box');
		var mapbox = document.createElement("script");
		mapbox.type = "text/javascript";
		mapbox.src = "https://api.mapbox.com/mapbox.js/v2.2.1/mapbox.js";
		mapbox.defer = true;
		colissimo("head").append(mapbox);
		colissimo("head").append('\n');

		var mapbox_css_link = colissimo("<link>", {
			rel: "stylesheet",
			type: "text/css",
			href:"https://api.mapbox.com/mapbox.js/v2.2.1/mapbox.css"
		});
		mapbox_css_link.appendTo('head');
		colissimo("head").append('\n');


		//Les balises meta
		colissimo("head").append('<meta http-equiv="X-UA-Compatible" content="IE=edge">');
		colissimo("head").append('\n');
		colissimo("head").append('<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />');
		colissimo("head").append('\n');
		colissimo("head").append('<meta name="apple-mobile-web-app-capable" content="yes">');
		colissimo("head").append('\n');


		//console.log('FIN integre les scripts pour la map');




		var scroll = document.createElement("script");
		scroll.type = "text/javascript";
		scroll.src = urlColiposte + "/widget-point-retrait/resources/js/jquery.jscrollpane.min.js";
		scroll.defer = true;
		colissimo("head").append(scroll);
		colissimo("head").append('\n');

		var mouse = document.createElement("script");
		mouse.type = "text/javascript";
		mouse.src = urlColiposte + "/widget-point-retrait/resources/js/jquery.mousewheel.js";
		mouse.defer = true;
		colissimo("head").append(mouse);
		colissimo("head").append('\n');

		var scrollbar = document.createElement("script");
		scrollbar.type = "text/javascript";
		scrollbar.src = urlColiposte + "/widget-point-retrait/resources/js/jquery.scrollbar.js";
		scrollbar.defer = true;
		var widget_url = urlColiposte + "/widget-point-retrait/index.htm";
//		

		//console.log('liste pays = ' + params.ceCountryList);
		var colissimo_widget_lement = this;

		colissimo.ajax({
			method :"POST",
			url: widget_url,
			data : 'h1=' + lang + '&callBackFrame=' + callBackFrame + '&domain=' + urlColiposte + '&ceCountryList=' + params.ceCountryList + '&codeRetour=' + codeRetour + '&dyPreparationTime=' + params.dyPreparationTime + '&ceAddress=' + params.ceAddress + '&ceCountry=' + params.ceCountry+ '&ceZipCode=' + params.ceZipCode + '&token=' + params.token,
			success: function (data) {
				colissimo_widget_lement.html( data );

				//Le pays
				if(params.ceCountry != null && params.ceCountry != '')
				{
					//console.log('pays par defaut : ' + params.ceCountry);
					colissimo("#listePays").attr('value', params.ceCountry);
				}

				setTimeout( function() {

					if(params.ceAddress != null && params.ceAddress != '')
					{
						colissimo("#Adresse1").val(params.ceAddress);
					}
					if(params.ceZipCode != null && params.ceZipCode != '')
					{
						colissimo("#CodePostal").val(params.ceZipCode);
					}
					if(params.ceTown != null && params.ceTown != '')
					{
						colissimo("#Ville").val(params.ceTown);
						setTimeout( function() {
							getPointsRetrait();
						}, 1000 )
					}

				}, 500 )

			},
			error : function(resultat, statut, erreur){
				alert(statut);
				alert(erreur);
			}
		});
		var $ = jQuery.noConflict();
		return this;
	},
	frameColissimoClose: function()
	{
		//Code du second plug-in ici
		//console.log('fermeture frame');
		return this;
	}
});
