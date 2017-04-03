datatable.on('click', '[id^="btn-delete-"]', function (e) {
    e.preventDefault();

    var url = $(this).data('url');

    swal({
        title: "Do you really want to delete this data?",
        text: "You will not be able to recover this data!",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete it!",
        closeOnConfirm: true
    }, function () {
        $.ajax({
            url : url,
            type : 'DELETE',
            beforeSend: function (xhr) {
                return xhr.setRequestHeader('X-CSRF-TOKEN', window.Laradmin.csrfToken);
            }
        }).always(function (data) {
            window.location.reload();
        });
    });
});