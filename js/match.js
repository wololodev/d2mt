(function($) {
	"use strict";
	$.ajaxSetup({
		type: "GET",
		dataType: "json",
		success: function() {
			$preloadGif.remove();
		},
		error: function() {
			$preloadGif.attr('class', 'err').html("Either <a href='" + d2mt.config.joinDotaUrl + "'>" +
					"joinDota</a> or <a href='" + d2mt.config.gosugamersUrl + "'>" + "GosuGamers</a> " +
					"is down or you need to <a href='" + d2mt.config.exturl + "'>upgrade</a>. Click refresh to " +
					"retry. If problems persist contact me as soon as possible: <a href='mailto:" +
					d2mt.config.email + "'>" + "dota@hotmail.ca</a>");
		}
	});

	var d2mt = {
		config: {
			version: "1.5.2",
			browser: "chrome",
			email: "dota@hotmail.ca",
			exturl: "https://chrome.google.com/webstore/detail/dota-2-match-ticker/nejdjlaibiicicciokonbbkecjleilon",
			joinDotaUrl: "http://www.joindota.com/",
			gosugamersUrl: "http://www.gosugamers.net/dota2"
		},
		settings: {
			isSpoilerOn: localStorage.isSpoilerOn === "true",
			isPopout: localStorage.isPopout === "true"
		},
		nodes: {
			preloadGif: $('.gif'),
			jdRecentResults: $('#finishedList'),
			ggRecentResults: $('#gg_finishedList'),
			jdMatches: $('#jd_matches'),
			ggMatches: $('#gg_acc_matches'),
			rankings: $('#rankings')
		},
		init: function() {
			defineDefaults();
			onLoadAjax();
		}
	};

	// Cache Settings
	var isSpoilerOn = d2mt.settings.isSpoilerOn,
		isPopout = d2mt.settings.isPopout;

	// Cache Nodes
	var $preloadGif = d2mt.nodes.preloadGif,
		$jdRecentResults = d2mt.nodes.jdRecentResults,
		$ggRecentResults = d2mt.nodes.ggRecentResults,
		$jdMatches = d2mt.nodes.jdMatches,
		$ggMatches = d2mt.nodes.ggMatches,
		$rankings = d2mt.nodes.rankings;

	var setResultsSpoiler = function(isSpoilerOn) {
		var result;
		var $winResults = $('.winResult');
		var $jdSeriesResults = $('.series');

		if (isSpoilerOn) {
			$winResults.text("?");
			$jdSeriesResults.addClass('opaque');
			$($jdRecentResults, $ggRecentResults).find('b').addClass("unboldWinner");
		} else {
			$jdSeriesResults.removeClass('opaque');
			$('.unboldWinner').removeClass("unboldWinner");
			$winResults.each(function(){
				result = $(this).data('winner');
				$(this).text(result);
			});
		}
	};

	var setStreamLink = function(isPopout) {
		var id;
		if (isPopout) {
			$('.twitch').each(function(){
				id = $(this).data("id");
				$(this).attr("href", "http://www.twitch.tv/" + id + "/popout");
			});
		} else {
			$('.twitch').each(function(){
				id = $(this).data("id");
				$(this).attr("href", "http://www.twitch.tv/" + id);
			});
		}
	};

	var setTime = function() {
		$('.push-tt').each(function(){
			var timestamp = $(this).attr('alt');
			var newDate = new Date(timestamp*1000);
			newDate.setHours(newDate.getHours());
			var fulldate = newDate.format(localStorage.dateFormat + localStorage.timeFormat);
			var prevEventTime = $(this).parent().attr('data-original-title');
			var newEventTime = prevEventTime + "<br>" + fulldate;
			$(this).parent().attr('data-original-title', newEventTime);
		});
	};

	var setUpdatedTime = function() {
		$('.push-tt').each(function(){
			var timestamp = $(this).attr('alt');
			var newDate = new Date(timestamp*1000);
			newDate.setHours(newDate.getHours());
			var fulldate = newDate.format(localStorage.dateFormat + localStorage.timeFormat);
			var prevEventStr = $(this).parent().attr('data-original-title');
			var prevEventIndex = prevEventStr.indexOf('<br>');
			var prevEventTime = prevEventStr.substring(0, prevEventIndex);
			var newEventTime = prevEventTime + "<br>" + fulldate;
			$(this).parent().attr('data-original-title', newEventTime);
		});
	};

	var defineDefaults = function() {
		// Last Opened Tab
		if (localStorage.lastOpenedTab !== undefined) {
			$('#'+localStorage.lastOpenedTab).tab('show');
		} else {
			$('#nav_jd').tab('show');
		}

		// Time Format
		if (localStorage.timeFormat !== undefined) {
			if ("H:MM Z" === localStorage.timeFormat)
				$('#twfh').addClass('active');
			else {
				localStorage.timeFormat = "h:MMTT Z";
				$('#PM').addClass('active');
			}
		} else {
			localStorage.timeFormat = "H:MM Z";
			$('#twfh').addClass('active');
		}

		// Date Format
		if (localStorage.dateFormat !== undefined) {
			if ("d/mm/yyyy " === localStorage.dateFormat)
				$('#dateInt').addClass('active');
			else
				$('#dateUS').addClass('active');
		} else {
			localStorage.dateFormat = "d/mm/yyyy ";
			$('#dateInt').addClass('active');
		}

		// Stream Link Format
		if (isPopout) {
			$('#spTrue').addClass("active");
		} else {
			$('#spFalse').addClass("active");
		}

		// Spoiler
		if (isSpoilerOn) {
			$('#spoilerTrue').addClass("active");
		} else {
			$('#spoilerFalse').addClass("active");
		}
	};

	var onLoadAjax = function() {
		// NEWS COVERAGE
		var load_news = $.ajax("http://api.dotaprj.me/news/v150/api.json")
		.success(function(data) {
			var jdnews, ggnews;
			$.each(data, function(key, val) {
				if (key === "jd") {
					jdnews += val;
				} else {
					ggnews += val;
				}
			});

			$('#tbody_jdNews').append(jdnews);
			$('#tbody_ggNews').append(ggnews);
		});

		// JOINDOTA MATCH TICKER
		var load_jdmatches = $.ajax("http://api.dotaprj.me/jd/matches/v130/api.json")
		.success(function(data) {
			var recent, finished;
			$.each(data, function(key, val) {
				if (key === "eventDone") {
					finished += val;
				} else {
					recent += val;
				}
			});

			$('#tbody_jdUpMatches').append(recent);
			$('#tbody_jdReMatches').append(finished);
		});

		// GOSUGAMERS MATCH TICKER
		var load_ggmatches = $.ajax("http://api.dotaprj.me/gg/matches/v120/api.json")
		.success(function(data) {
			var recent, finished;
			$.each(data, function(key, val) {
				if (key === "eventSoon") {
					recent += val;
				} else {
					finished += val;
				}
			});

			$('#tbody_ggUpMatches').append(recent);
			$('#tbody_ggReMatches').append(finished);
		});


		// RANKINGS AND STANDINGS
		var load_rankings = $.ajax("http://api.dotaprj.me/rankings/v150/api.json")
		.success(function(data) {
			var jdrankings, ggrankings;
			$.each(data, function(key, val) {
				if (key === "jd") {
					jdrankings += val;
				} else {
					ggrankings += val;
				}
			});

			$('#tbody_jdRankings').append(jdrankings);
			$('#tbody_ggRankings').append(ggrankings);
		});

		// VODS AND STREAMS
		var load_streamsAndVods = $.ajax("http://api.dotaprj.me/stream/v151/api.json")
		.success(function(data) {
			var streams, vods;
			$.each(data, function(key, val) {
				if (key === "stream") {
					streams += val;
				} else {
					vods += val;
				}
			});

			$('#tbody_streams').append(streams);
			$('#tbody_vods').append(vods);
			setStreamLink(isPopout);
		});

		$.when(load_news, load_jdmatches, load_ggmatches, load_rankings, load_streamsAndVods).done(function() {
			if (isSpoilerOn) {
				setResultsSpoiler(true);
			}
			$('.d2mtrow').tooltip({html:true});
			setTime();
		});
	};

	var update = function() {
		$('#jd_matches tr, #gg_acc_matches tr, #streams_vods tr, #news tr, #rankings tr, .err, .tooltip').remove();
		$('.gif').show();
		onLoadAjax();
	};

	d2mt.init();


	$('#jd_matches, #gg_acc_matches, #streams_vods, #rankings, #news').on('click', 'tr', function(){
		var url = $(this).attr('href');
		window.open(url);
	});

	$('a[data-toggle="tab"]').on('shown', function(e) {
		var lastTab = e.target;
		localStorage.lastOpenedTab = $(lastTab).attr('id');
	});

	$('.timeformat').click(function(){
		localStorage.timeFormat = $(this).attr('alt');
		setUpdatedTime();
	});

	$('.dateformat').click(function(){
		localStorage.dateFormat = $(this).attr('alt');
		setUpdatedTime();
	});

	$('.spformat').click(function(){
		isPopout = $(this).data('ispopout');
		localStorage.isPopout = isPopout;
		setStreamLink(isPopout);
	});

	$('.spoilerformat').click(function(){
		isSpoilerOn = $(this).data('isspoiler');
		localStorage.isSpoilerOn = isSpoilerOn;
		setResultsSpoiler(isSpoilerOn);
	});

	$('#nav_update').click(function(){
		update();
	});

	$('.permalink').click(function(e){
		var url = $(this).attr("data-link");
		window.open(url);
		e.stopPropagation();
	});

	$jdRecentResults.on('mouseover mouseout', '.eventDone', function(e) {
		if (isSpoilerOn) {
			var $closeResNode = $(this).find('.winResult');
			var $jdWinnerSeries = $(this).find('.series');
			var $winner = $(this).find('b');
			if (e.type === 'mouseover') {
				var result = $closeResNode.attr('data-winner');
				$closeResNode.text(result);
				$jdWinnerSeries.removeClass('opaque');
				$winner.removeClass("unboldWinner");
			} else {
				$jdWinnerSeries.addClass('opaque');
				$closeResNode.text("?");
				$winner.addClass("unboldWinner");
			}
		}
	});

	$ggRecentResults.on('mouseover mouseout', '.eventDone', function(e) {
		if (isSpoilerOn){
			var $closeResNode = $(this).find('.winResult');
			var $winner = $(this).find('b');
			if (e.type === 'mouseover') {
				var result = $closeResNode.attr('data-winner');
				$closeResNode.text(result);
				$winner.removeClass("unboldWinner");
			} else {
				$closeResNode.text("?");
				$winner.addClass("unboldWinner");
			}
		}
	});
})(jQuery);