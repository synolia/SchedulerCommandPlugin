function fireLog(e) {
  e.preventDefault();
  var url = $(e.currentTarget).data('href');
  $.get(url, function (data) {
    $('#log-content').html($(data));
  }).done(function () {
    $('#log-modal').modal('show');
  })
}

function closeLog(e) {
  e.preventDefault();
  $('#log-modal').modal('hide');
  //clear updateLog w/o knowing its id
  for (i = 0; i < 10000; i++) {
    window.clearInterval(i);
  }
}
