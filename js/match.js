(function($) {
	"use strict";

	var d2mt = {
		settings: {
			isSpoilerOn: localStorage.isSpoilerOn == "true"
		}
	};

	var isSpoilerOn = d2mt.settings.isSpoilerOn;

	var setResultsSpoiler = function(isSpoilerOn) {
		var result,
			$winResultNode = $('.winResult');

		if (isSpoilerOn) {
			$winResultNode.text("?");
			$('.series').css('opacity', '0');
			$('#finishedList, #gg_finishedList').find('b').addClass("unboldWinner");
		} else {
			$('.series').css('opacity', '1');
			$('.unboldWinner').removeClass("unboldWinner");
			$winResultNode.each(function(){
				result = $(this).attr('data-winner');
				$(this).text(result);
			});
		}
	};

	var setStreamLink = function(isPopout) {
		if (isPopout === "spTrue") {
			$('.twitch').each(function(){
				var id = $(this).attr("data-id");
				$(this).attr("href", "http://www.twitch.tv/" + id + "/popout");
			});
		} else {
			$('.twitch').each(function(){
				var id = $(this).attr("data-id");
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
		if (localStorage.isPopout !== undefined) {
			if (localStorage.isPopout === "spTrue") {
				$('#spTrue').addClass("active");
			} else {
				$('#spFalse').addClass("active");
			}
		} else {
			localStorage.isPopout = "spFalse";
			$('#spFalse').addClass("active");
		}

		// Spoiler
		if (isSpoilerOn !== undefined) {
			if (isSpoilerOn) {
				$('#spoilerTrue').addClass("active");
			} else {
				$('#spoilerFalse').addClass("active");
			}
		} else {
			localStorage.isSpoilerOn = false;
			$('#spoilerFalse').addClass("active");
		}
	};
	
	var onLoadAjax = function() {
		// JOINDOTA MATCH TICKER
		$.ajax({
			url: "http://api.dotaprj.me/jd/matches/v130/api.json",
			dataType: 'json',
			success: function(data) {
				var eventLive = [];
				var eventSoon = [];
				var eventDone = [];
				var eventDoneExtra = [];
				
				$.each(data, function(key, val) {
					switch (key) {
						case "eventLive":
							eventLive.push(val);
							break;
						case "eventSoon":
							eventSoon.push(val);
							break;
						default:
							eventDone.push(val);
					}
				});
				
				var recent = eventLive + eventSoon;
				var finished = eventDone + eventDoneExtra;
				$('#acc_matches .gif').hide();
				$('#matchList > tbody').html(recent);
				$('#finishedList > tbody').html(finished);
				$('#acc_matches tr').tooltip({html:true});
				setResultsSpoiler(isSpoilerOn);
				setTime(0, ".jd_date");
			},
			error: function() {
				$('#acc_matches .gif').attr('class', 'err').html("Somewhere, somehow, went wrong. Please update to the <a href='https://chrome.google.com/webstore/detail/dota-2-match-ticker/nejdjlaibiicicciokonbbkecjleilon' target='_blank'>latest version.</a>");
			}
		});

		// GOSUGAMERS MATCH TICKER
		$.ajax({
			url: "http://api.dotaprj.me/gg/matches/v120/api.json",
			dataType: 'json',
			success: function(data) {
				var eventLive = [];
				var eventSoon = [];
				var eventDone = [];
				var eventDoneExtra = [];
				
				$.each(data, function(key, val) {
					switch (key) {
						case "eventLive":
							eventLive.push(val);
							break;
						case "eventSoon":
							eventSoon.push(val);
							break;
						default:
							eventDone.push(val);
					}
				});
				
				var recent = eventLive + eventSoon;
				var finished = eventDone + eventDoneExtra;
				$('#gg_acc_matches .gif').hide();
				$('#gg_matchList > tbody').html(recent);
				$('#gg_finishedList > tbody').html(finished);
				$('#gg_acc_matches tr').tooltip({html:true});
				setResultsSpoiler(isSpoilerOn);
				setTime(0, ".gg_date");
			},
			error: function() {
				$('#gg_acc_matches .gif').attr('class', 'err').html("Somewhere, somehow, went wrong. Please update to the <a href='https://chrome.google.com/webstore/detail/dota-2-match-ticker/nejdjlaibiicicciokonbbkecjleilon' target='_blank'>latest version.</a>");
			}
		});

		// RANKINGS AND STANDINGS
		$.ajax({
			url: "http://api.dotaprj.me/rankings/v150/api.json",
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
				$('#rankings .gif').hide();
				$('#jd_rankList > tbody').html(jdList);
				$('#gg_rankList > tbody').html(ggList);
				$('#rankings tr').tooltip({html:true});
			},
			error: function() {
				$('#rankings .gif').attr('class', 'err').html("Somewhere, somehow, went wrong. Please update to the <a href='https://chrome.google.com/webstore/detail/dota-2-match-ticker/nejdjlaibiicicciokonbbkecjleilon' target='_blank'>latest version.</a>");
			}
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
				setStreamLink(localStorage.isPopout);
			},
			error: function() {
				$('#streams_vods .gif').attr('class', 'err').html("Somewhere, somehow, went wrong. Please update to the <a href='https://chrome.google.com/webstore/detail/dota-2-match-ticker/nejdjlaibiicicciokonbbkecjleilon' target='_blank'>latest version.</a>");
			}
		});
	};
	
	var update = function() {
		$('#acc_matches tr, #gg_acc_matches tr, #streams_vods tr, #news tr, #rankings tr, .err, .tooltip').remove();
		$('.gif').show();
		onLoadAjax();
	};

	defineDefaults();
	onLoadAjax();

	$('#finishedList').on('mouseover mouseout', '.eventDone', function(e) {
		if (isSpoilerOn){
			var $closeResNode = $(this).find('.winResult');
			var $jdWinnerSeries = $(this).find('.series');
			var $winner = $(this).find('b');
			if (e.type === 'mouseover') {
				var result = $closeResNode.attr('data-winner');
				$closeResNode.text(result);
				$jdWinnerSeries.css('opacity', '1');
				$winner.removeClass("unboldWinner");
			} else {
				$jdWinnerSeries.css('opacity', '0');
				$closeResNode.text("?");
				$winner.addClass("unboldWinner");
			}
		}
	});

	$('#gg_finishedList').on('mouseover mouseout', '.eventDone', function(e) {
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

	$('#acc_matches, #gg_acc_matches, #streams_vods, #rankings, #news').on('click', 'tr', function(){
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
		localStorage.isPopout = $(this).attr('alt');
		setStreamLink(localStorage.isPopout);
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