$(document).ready(function () {
  //Last know size of the file
  var lastSize = 0;
  //Grep keyword
  var grep = "";
  //Should the Grep be inverted?
  var invert = 0;
  //Last known document height
  var documentHeight = 0;
  //Last known scroll position
  var scrollPosition = 0;
  //Should we scroll to the bottom?
  var scroll = false;

  //Close the settings dialog after a user hits enter in the textarea
  $('#grep').keyup(function (e) {
    if (e.keyCode === 13) {
      lastSize = 0;
      grep = $(this).val();
      $('#results').html('');
    }
  });
  //Focus on the textarea
  $("#grep").focus();
  //Settings button into a nice looking button with a theme
  //Settings button opens the settings dialog
  $("#grepKeyword").click(function () {
    $("#settings").dialog('open');
    $("#grepKeyword").removeClass('ui-state-focus');
  });
  $(".file").click(function (e) {
    $("#results").text("");
    lastSize = 0;
  });

  //Set up an interval for updating the log. Change updateTime in the PHPTail constructor to change this
  var updateLog = setInterval(function () {
    //This function queries the server for updates.
    $.getJSON(sy_route + '?refresh=1&lastsize=' + lastSize + '&grep-keywords=' + grep + '&invert=' + invert, function (data) {
      lastSize = data.size;
      $("#current").text(data.file);
      $.each(data.data, function (key, value) {
        $("#results").prepend('' + value + '<br/>');
      });
      if (scroll) {
        scrollToBottom();
      }
      if (data.data.length < 1) {
        $("#results .loader").remove();
      }
    })
      .fail(function (jqXHR) {
        $("#results").append(jqXHR.responseText);
        clearInterval(updateLog);
      });
  }, sy_updateTime);

  //Some window scroll event to keep the menu at the top
  $(window).scroll(function (e) {
    if ($(window).scrollTop() > 0) {
      $('.float').css({
        position: 'fixed',
        top: '0',
        left: 'auto',
      });
    } else {
      $('.float').css({
        position: 'static',
      });
    }
  });
  //If window is resized should we scroll to the bottom?
  $(window).resize(function () {
    if (scroll) {
      scrollToBottom();
    }
  });
  //Handle if the window should be scrolled down or not
  $(window).scroll(function () {
    documentHeight = $(document).height();
    scrollPosition = $(window).height() + $(window).scrollTop();
    scroll = documentHeight <= scrollPosition;
  });
  scrollToBottom();
});

//This function scrolls to the bottom
function scrollToBottom() {
  $("html, body").animate({ scrollTop: $(document).height() }, "fast");
}
