(function($) {
	"use strict";
	$.ajaxSetup({
		type: "GET",
		dataType: "json"
	});

	var d2mt = {
		config: {
			version: "1.5.2",
			browser: "chrome",
			exturl: "https://chrome.google.com/webstore/detail/dota-2-match-ticker/nejdjlaibiicicciokonbbkecjleilon",
			joinDotaUrl: "http://www.joindota.com/",
			gosugamersUrl: "http://www.gosugamers.net/dota2"
		},
		settings: {
			isSpoilerOn: localStorage.isSpoilerOn === "true",
			isPopout: localStorage.isPopout === "true"
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
		}
	};

	// Cache Settings
	var isSpoilerOn = d2mt.settings.isSpoilerOn,
		isPopout = d2mt.settings.isPopout;

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

	var setTime = function(phm, cssClass) {
		$(cssClass).each(function(){
			var timestamp = $(this).attr('alt');
			var newDate = new Date(timestamp*1000);
			newDate.setHours(newDate.getHours() + phm);
			var fulldate = newDate.format(localStorage.dateFormat + localStorage.timeFormat);
			var prevEventTime = $(this).parent().attr('data-original-title');
			var newEventTime = prevEventTime + "<br>" + fulldate;
			$(this).parent().attr('data-original-title', newEventTime);
		});
	};

	var setUpdatedTime = function() {
		$('.jd_date, .gg_date, .vod_date, .news_date').each(function(){
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
		// JOINDOTA MATCH TICKER
		var load_jdmatches = $.ajax("http://api.dotaprj.me/jd/matches/v130/api.json")
		.success(function(data) {
			var recent, finished;
			$jdMatches.find('.gif').hide();
			$.each(data, function(key, val) {
				if (key === "eventDone") {
					finished += val;
				} else {
					recent += val;
				}
			});

			$('#tbody_jdUpMatches').append(recent);
			$('#tbody_jdReMatches').append(finished);
			$jdMatches.find('tr').tooltip({html:true});
			setTime(0, ".jd_date");
		}).error(function() {
			$jdMatches.find('.gif').attr('class', 'err').html("Either <a href='" + d2mt.config.joinDotaUrl + "'>" +
					"joinDota</a> is down or you need to <a href='" + d2mt.config.exturl + "'>upgrade.</a>");
		});

		// GOSUGAMERS MATCH TICKER
		var load_ggmatches = $.ajax("http://api.dotaprj.me/gg/matches/v120/api.json")
		.success(function(data) {
			var recent, finished;
			$ggMatches.find('.gif').hide();
			$.each(data, function(key, val) {
				if (key === "eventSoon") {
					recent += val;
				} else {
					finished += val;
				}
			});

			$('#tbody_ggUpMatches').append(recent);
			$('#tbody_ggReMatches').append(finished);
			$ggMatches.find('tr').tooltip({html:true});
			setTime(0, ".gg_date");
		}).error(function() {
			$ggMatches.find('.gif').attr('class', 'err').html("Either <a href='" + d2mt.config.gosugamersUrl + "'>" +
					"GosuGamers</a> is down or you need to <a href='" + d2mt.config.exturl + "'>upgrade.</a>");
		});

		$.when(load_jdmatches, load_ggmatches).done(function() {
			if (isSpoilerOn) {
				setResultsSpoiler(isSpoilerOn);
			}
		});

		// RANKINGS AND STANDINGS
		var load_rankings = $.ajax("http://api.dotaprj.me/rankings/v150/api.json")
		.success(function(data) {
			var jdrankings, ggrankings;
			$rankings.find('.gif').hide();
			$.each(data, function(key, val) {
				if (key === "jd") {
					jdrankings += val;
				} else {
					ggrankings += val;
				}
			});

			$('#tbody_jdRankings').append(jdrankings);
			$('#tbody_ggRankings').append(ggrankings);
			$rankings.find('tr').tooltip({html:true});
		}).error(function() {
			$rankings.find('.gif').attr('class', 'err').html("Either <a href='" + d2mt.config.gosugamersUrl + "'>" +
					"GosuGamers</a> is down or you need to <a href='" + d2mt.config.exturl + "'>upgrade.</a>");
		});

		// NEWS COVERAGE
		$.ajax({
			url: "http://api.dotaprj.me/news/v150/api.json",
			dataType: 'json',
			success: function(data) {
				var jd = [];
				var gg = [];
				var tmp = [];
				
				$.each(data, function(key, val) {
					switch (key) {
						case "jd":
							jd.push(val);
							break;
						default:
							gg.push(val);
					}
				});
				
				var jdList = jd + tmp;
				var ggList = gg + tmp;
				$('#news .gif').hide();
				$('#jdNewsList > tbody').html(jdList);
				$('#ggNewsList > tbody').html(ggList);
				$('#news tr').tooltip({html:true});
				setTime(0, ".news_date");
			},
			error: function() {
				$('#news .gif').attr('class', 'err').html("Somewhere, somehow, went wrong. Please update to the <a href='https://chrome.google.com/webstore/detail/dota-2-match-ticker/nejdjlaibiicicciokonbbkecjleilon' target='_blank'>latest version.</a>");
			}
		});
		
		// VODS AND STREAMS
		$.ajax({
			url: "http://api.dotaprj.me/stream/v151/api.json",
			dataType: 'json',
			success: function(data) {
				var streams = [];
				var vods = [];
				var tmp = [];
				
				$.each(data, function(key, val) {
					switch (key) {
						case "stream":
							streams.push(val);
							break;
						default:
							vods.push(val);
					}
				});
				
				var streamList = streams + tmp;
				var vodsList = vods + tmp;
				$('#streams_vods .gif').hide();
				$('#streamList > tbody').html(streamList);
				$('#vodsList > tbody').html(vodsList);
				$('#streams_vods tr').tooltip({html:true});
				setTime(0, ".vod_date");
				setStreamLink(isPopout);
			},
			error: function() {
				$('#streams_vods .gif').attr('class', 'err').html("Somewhere, somehow, went wrong. Please update to the <a href='https://chrome.google.com/webstore/detail/dota-2-match-ticker/nejdjlaibiicicciokonbbkecjleilon' target='_blank'>latest version.</a>");
			}
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
})(jQuery);