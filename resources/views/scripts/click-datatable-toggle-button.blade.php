datatable.on('click', '[id^="btn-toggle-"]', function (e) {
    e.preventDefault();

    var url = $(this).data('url');

    $.ajax({
        url: url,
        type: "POST",
        data: {
            _token: '{{ csrf_token() }}'
        },
        success: function (districts) {
            var row = $(this).closest('tr');
            datatable.row(row).draw();
        },
        error: function () {
        }
    });
});