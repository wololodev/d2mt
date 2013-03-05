(function($) {
	"use strict";
	$.ajaxSetup({
		type: "GET",
		dataType: "json",
		success: function() {
			$('.gif').remove();
		},
		error: function() {
			$('.gif').attr('class', 'err').html("Either <a href='" + d2mt.config.joinDotaUrl + "'>" +
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
			isPopout: localStorage.isPopout === "true",
			timeFormat: localStorage.timeFormat === "h:MMTT Z" ? "h:MMTT Z" : "H:MM Z",
			dateFormat: localStorage.dateFormat === "d/mm/yyyy" ? "d/mm/yyyy" : "mm/d/yyyy"
		},
		nodes: {
			jdRecentResults: $('#finishedList'),
			ggRecentResults: $('#gg_finishedList'),
			jdMatches: $('#jd_matches'),
			ggMatches: $('#gg_acc_matches'),
			rankings: $('#rankings')
		},
		init: function() {
			defineDefaults();
			onLoadAjax();
			_gaq.push(['_trackPageview']);
		}
	};

	// Cache Settings
	var isSpoilerOn = d2mt.settings.isSpoilerOn,
		isPopout = d2mt.settings.isPopout,
		timeFormat = d2mt.settings.timeFormat,
		dateFormat = d2mt.settings.dateFormat;

	// Cache Nodes
	var $jdRecentResults = d2mt.nodes.jdRecentResults,
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
			var $parentNode = $(this).parent();
			var timestamp = $(this).attr('alt');
			var newDate = new Date(timestamp*1000);
			newDate.setHours(newDate.getHours());
			var fulldate = format(newDate, dateFormat + " " + timeFormat);
			var prevEventTime = $parentNode.attr('data-original-title');
			var newEventTime = prevEventTime + "<br>" + fulldate;
			$parentNode.attr('data-original-title', newEventTime);
		});
	};

	var setUpdatedTime = function() {
		$('.push-tt').each(function(){
			var $parentNode = $(this).parent();
			var timestamp = $(this).attr('alt');
			var newDate = new Date(timestamp*1000);
			newDate.setHours(newDate.getHours());
			var fulldate = format(newDate, dateFormat + " " + timeFormat);
			var prevEventStr = $parentNode.attr('data-original-title');
			var prevEventIndex = prevEventStr.indexOf('<br>');
			var prevEventTime = prevEventStr.substring(0, prevEventIndex);
			var newEventTime = prevEventTime + "<br>" + fulldate;
			$parentNode.attr('data-original-title', newEventTime);
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
		if ("H:MM Z" === timeFormat)
			$('#twfh').addClass('active');
		else {
			$('#PM').addClass('active');
		}

		// Date Format
		if ("d/mm/yyyy" === dateFormat) {
			$('#dateInt').addClass('active');
		} else {
			$('#dateUS').addClass('active');
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
				} else if (key === "eventSoon") {
					recent += val;
				} else {
					recent = val + recent;
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
				if (key === "eventDone") {
					finished += val;
				} else {
					recent += val;
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
		$('.d2mtrow, .err, .tooltip').remove();
		$('.listload').html("<tr class='gif'></tr>");
		onLoadAjax();
	};

	// Start Main
	d2mt.init();

	$('.tab-content').on('click', '.d2mtrow', function(){
		var url = $(this).attr('href');
		window.open(url);
	});

	$('.menutab').on('shown', function(e) {
		var lastTab = e.target;
		localStorage.lastOpenedTab = $(lastTab).attr('id');
		_gaq.push(['_trackEvent', e.target.id, 'clicked']);
		_gaq.push(['_trackPageview']);
	});

	$('.timeformat').click(function(){
		timeFormat = $(this).attr('alt');
		localStorage.timeFormat = timeFormat;
		setUpdatedTime();
	});

	$('.dateformat').click(function(){
		dateFormat = $(this).attr('alt');
		localStorage.dateFormat = dateFormat;
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
			var result;
			var $closeResNode = $(this).find('.winResult');
			var $jdWinnerSeries = $(this).find('.series');
			var $winner = $(this).find('b');
			if (e.type === 'mouseover') {
				result = $closeResNode.attr('data-winner');
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
			var result;
			var $closeResNode = $(this).find('.winResult');
			var $winner = $(this).find('b');
			if (e.type === 'mouseover') {
				result = $closeResNode.attr('data-winner');
				$closeResNode.text(result);
				$winner.removeClass("unboldWinner");
			} else {
				$closeResNode.text("?");
				$winner.addClass("unboldWinner");
			}
		}
	});
})(jQuery);