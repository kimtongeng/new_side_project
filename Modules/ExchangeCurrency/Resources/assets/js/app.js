$(document).ready(function () {
  //Exchange currency table
  // var exchange_currency = $('#exchange_currency').DataTable({
  //     processing: true,
  //     serverSide: true,
  //     fixedHeader: false,
  //     ajax: '/exchange_currency',
  //     columnDefs: [
  //         {
  //             targets: 8,
  //             orderable: false,
  //             searchable: false,
  //         },
  //     ],
  //     //
  // });
  var exchange_currency = $('#exchange_currency').DataTable({
      processing: true,
      serverSide: true,
      ajax: '/exchange_currency',
      columns: [
          { data: 'country', name: 'country' },
          { data: 'currency', name: 'currency' },
          { data: 'code', name: 'code' },
          { data: 'symbol', name: 'symbol' },
          { data: 'exchange_rate', name: 'exchange_rate' },

          { data: 'is_use', name: 'is_use' },
          { data: 'action', name: 'action', orderable: false, searchable: false },
      ],
  });

  $(document).on('click', 'button.edit_exchange_currency', function () {
      $('div.exchange_currency_modal').load($(this).data('href'), function () {
          $(this).modal('show');

          $('form#edit_exchange_currency').submit(function (e) {
              e.preventDefault();
              var form = $(this);
              var data = form.serialize();

              $.ajax({
                  method: 'POST',
                  url: $(this).attr('action'),
                  dataType: 'json',
                  data: data,
                  beforeSend: function (xhr) {
                      __disable_submit_button(form.find('button[type="submit"]'));
                  },
                  success: function (result) {
                      if (result.success == true) {
                          $('div.exchange_currency_modal').modal('hide');
                          toastr.success(result.msg);
                          exchange_currency.ajax.reload();
                      } else {
                          toastr.error(result.msg);
                      }
                  },
              });
          });
      });
  });
  $(document).on('submit', 'form#exchange_currency', function (e) {
      e.preventDefault();
      var form = $(this);
      var data = form.serialize();

      $.ajax({
          method: 'POST',
          url: $(this).attr('action'),
          dataType: 'json',
          data: data,
          beforeSend: function (xhr) {
              __disable_submit_button(form.find('button[type="submit"]'));
          },
          success: function (result) {
              if (result.success == true) {
                  $('div.exchange_currency_modal').modal('hide');
                  toastr.success(result.msg);
                  exchange_currency.ajax.reload();
              } else {
                  toastr.error(result.msg);
              }
          },
      });
  });
  $(document).on('click', 'button.delete_exchange_currency', function () {
      swal({
          title: LANG.sure,
          text: LANG.confirm_delete_exchange_currency,
          icon: 'warning',
          buttons: true,
          dangerMode: true,
      }).then((willDelete) => {
          if (willDelete) {
              var href = $(this).data('href');
              var data = $(this).serialize();

              $.ajax({
                  method: 'DELETE',
                  url: href,
                  dataType: 'json',
                  data: data,
                  success: function (result) {
                      if (result.success == true) {
                          toastr.success(result.msg);
                          exchange_currency.ajax.reload();
                      } else {
                          toastr.error(result.msg);
                      }
                  },
              });
          }
      });
  });
  $(document).on('click', '#toggle-status', function () {
      const isEnabled = $(this).hasClass('btn-success');
      if (isEnabled) {
          $(this).removeClass('btn-success').addClass('btn-danger').text('Disable');
          $('#is_use').val(0);
      } else {
          $(this).removeClass('btn-danger').addClass('btn-success').text('Enable');
          $('#is_use').val(1);
      }
  });

  // Handle the toggle button click
  $(document).on('click', 'button.update_status', function () {
      
      swal({
          title: LANG.sure,
          text: LANG.you_want_to_change_status,
          icon: 'warning',
          buttons: true,
          dangerMode: true,
      }).then((willDelete) => {
          if (willDelete) {
              var href = $(this).data('href');
              var data = $(this).serialize();

              $.ajax({
                  method: 'POST',
                  url: href,
                  dataType: 'json',
                  data: data,
                  success: function (result) {
                      if (result.success == true) {
                          toastr.success(result.msg);
                          exchange_currency.ajax.reload();
                      } else {
                          toastr.error(result.msg);
                      }
                  },
              });
          }
      });
  });
});
