// using https://cron-job.org for free crons to update this shit
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
      version: "1.6.2",
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
      dateFormat: localStorage.dateFormat === "d/mm/yyyy" ? "d/mm/yyyy" : "mm/d/yyyy",
      menuPos: localStorage.menuPos === "left" ? "left" : "top"
    },
    nodes: {
      jdRecentResults: $('#finishedList'),
      ggRecentResults: $('#gg_finishedList'),
      dd2RecentResults: $('#dd2_finishedList'),
      jdMatches: $('#jd_matches'),
      ggMatches: $('#gg_acc_matches'),
      dd2Matches: $('#dd2_acc_matches'),
      rankings: $('#rankings')
    },
    init: function() {
      defineDefaults();
      onLoadAjax();
    }
  };

  // Cache Settings
  var isSpoilerOn = d2mt.settings.isSpoilerOn,
    isPopout = d2mt.settings.isPopout,
    timeFormat = d2mt.settings.timeFormat,
    dateFormat = d2mt.settings.dateFormat,
    menuPos = d2mt.settings.menuPos;

  // Cache Nodes
  var $jdRecentResults = d2mt.nodes.jdRecentResults,
    $ggRecentResults = d2mt.nodes.ggRecentResults,
    $dd2RecentResults = d2mt.nodes.dd2RecentResults,
    $jdMatches = d2mt.nodes.jdMatches,
    $ggMatches = d2mt.nodes.ggMatches,
    $dd2Matches = d2mt.nodes.dd2Matches,
    $rankings = d2mt.nodes.rankings;

  var setResultsSpoiler = function(isSpoilerOn) {
    var result;
    var $winResults = $('.winResult');
    var $jdSeriesResults = $('.series');

    if (isSpoilerOn) {
      $winResults.text("?");
      $jdSeriesResults.addClass('opaque');
      $($jdRecentResults, $ggRecentResults, $dd2RecentResults).find('b').addClass("unboldWinner");
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

  var setMenuPosition = function(menuPos) {
    var $tab = $('.tabbable');
    if (menuPos === "top") {
      $tab.removeClass("tabs-left");
      $('.ph-tableft').removeClass("sub-tabs-left");
      $tab.addClass("tabs-top");
      $('.ph-tabstop').addClass("sub-tabs-top");
    } else {
      $tab.removeClass("tabs-top");
      $('.ph-tabstop').removeClass("sub-tabs-top");
      $tab.addClass("tabs-left");
      $('.ph-tableft').addClass("sub-tabs-left");
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
      $('.menutab:first').tab('show');
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

    // Spoiler
    if (menuPos === "top") {
      $('#menuTop').addClass("active");
    } else {
      $('#menuLeft').addClass("active");
      setMenuPosition();
    }
  };

  var endPoint = 'dotaprj.me/api';
  // var endPoint = 'localhost:8444/api';
  var onLoadAjax = function() {
    // DAILYDOTA2 MATCH TICKER
    var load_dd2matches = $.ajax("//" + endPoint + "/dd2/matches/v100/api.json")
    .success(function(data) {
      var recent, finished;
      $.each(data, function(key, val) {
        if (key === "eventDone") {
          finished += val;
        } else {
          recent += val;
        }
      });

      $('#tbody_dd2UpMatches').html(recent);
      $('#tbody_dd2ReMatches').html(finished);
    });


    // GOSUGAMERS MATCH TICKER
    var load_ggmatches = $.ajax("//" + endPoint + "/gg/matches/v120/api.json")
    .success(function(data) {
      var recent = "";
      var finished = "";
      $.each(data, function(key, val) {
        if (key === "eventDone") {
          finished += val;
        } else {
          recent += val;
        }
      });

      $('#tbody_ggUpMatches').html(recent);
      $('#tbody_ggReMatches').html(finished);
    });

    // VODS AND STREAMS
    var load_streamsAndVods = $.ajax("//" + endPoint + "/stream/v160/api.json")
    .success(function(data) {
      var streams, vods, dota2vods;
      $.each(data, function(key, val) {
        if (key === "stream") {
          streams += val;
        } else if (key === "dota2vods") {
          dota2vods += val;
        } else {
          vods += val;
        }
      });

      $('#tbody_streams').html(streams);
      $('#tbody_vods').html(vods);
      $('#tbody_d2vods').html(dota2vods);
      setStreamLink(isPopout);
    });

    $.when(load_dd2matches, load_ggmatches, load_streamsAndVods).done(function() {
      if (isSpoilerOn) {
        setResultsSpoiler(true);
      }

      $('.listload').each(function(i) {
        $(this).find('.d2mtrow:eq(0)').tooltip({
          html:true,
          placement: 'bottom'
        });
      });

      $('.d2mtrow').tooltip({
        html:true,
        placement: 'top'
      });
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

  $('.tab-content').on('click', '.d2mtrow', function(e){
    e.stopPropagation();
    var url = $(this).attr('href');
    window.open(url + "?utm_source=d2mt&utm_medium=rowClick&utm_campaign=Dota2MatchTicker");
  });

  $('.menutab').on('shown', function(e) {
    var lastTab = $(e.target).attr('id');
    localStorage.lastOpenedTab = lastTab;

    if (lastTab === "nav_stay22") {
      $("#stay22iframe").attr("src", "https://www.stay22.com/embed/epicenter-dota2-2016");
      // $("#stay22iframe").attr("src", "https://www.stay22.com/embed/the-manila-dota2-major-by-pgl");
    }
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

  $('.menu-position').click(function(){
    menuPos = $(this).data('position');
    localStorage.menuPos = menuPos;
    setMenuPosition(menuPos);
  });

  $('#nav_update').click(function(){
    update();
  });

  $('.permalink').click(function(e){
    var url = $(this).attr("data-link");
    window.open(url);
    e.stopPropagation();
  });

  $dd2RecentResults.on('mouseover mouseout', '.eventDone', function(e) {
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

  $('#nav_outpost').tooltip();

  setTimeout(function() {
    // Focus fix
    $("#nav_dd2").blur();
  }, 200);
})(jQuery);