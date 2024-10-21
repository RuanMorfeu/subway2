<?php
session_start();

// Verificar se a sessão existe
if (!isset($_SESSION['emailadm'])) {
    // Sessão não existe, redirecionar para outra página
    header("Location: ../login");
    exit();
}

// O restante do código da sua página continua aqui
// ...

?>


<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">


  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="keywords" content="Admin Dashboard" />
  <meta name="description" content="Admin Dashboard" />
  <meta name="robots" content="noindex,nofollow" />
  <title>Admin Dashboard</title>

  <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicon.png" />
  <link href="../assets/libs/flot/css/float-chart.css" rel="stylesheet" />
  <link href="../dist/css/style.min.css" rel="stylesheet" />

</head>

<body>
  <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full" data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">
    <header class="topbar" data-navbarbg="skin5">
      <nav class="navbar top-navbar navbar-expand-md navbar-dark">
        <div class="navbar-header" data-logobg="skin5">
          <a class="navbar-brand" href="../">
            <b class="logo-icon ps-2">
              <img src="../assets/images/logo-icon.png " alt="homepage" class="light-logo" width="25" />
            </b>
            <span class="logo-text ms-2">
                 <!-- <img
                  src="../assets/images/logo-text.webp"
                  width="150" height="50"
                  alt="homepage"
                  class="light-logo"
                /> -->
            </span>

          </a>

          <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i class="ti-menu ti-close"></i></a>
        </div>

        <div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin5">

          <ul class="navbar-nav float-start me-auto">
            <li class="nav-item d-none d-lg-block">
              <a class="nav-link sidebartoggler waves-effect waves-light" href="javascript:void(0)" data-sidebartype="mini-sidebar"><i class="mdi mdi-menu font-24"></i></a>
            </li>



          </ul>
        </div>
      </nav>
    </header>
    <?php include '../components/aside.php' ?>

    <div class="page-wrapper">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Tabela de Saques</h5>
          <div class="table-responsive">
            <table id="user-table" class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Email</th>
                  <th>Chave PIX</th>
                  <th>Valor</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody id="table-body">
                <!-- Dados da tabela serão inseridos aqui -->
              </tbody>
            </table>

          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="editModalLabel">Editar Saque</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form id="editForm">
              <input type="hidden" id="editId" name="id" />
              <input type="hidden" id="editEmail" name="email" />
              <input type="hidden" id="editExternalReference" name="externalreference" />
              <div class="form-group">
                <label for="editStatus">Status</label>
                <select class="form-control" id="editStatus" name="status">
                  <option value="cancelado">Cancelado</option>
                  <option value="aguardando">Aguardando</option>
                  <option value="aprovar">Aprovar</option>
                </select>
              </div>
              <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            </form>


          </div>
        </div>
      </div>
    </div>

    <script>
      $(document).ready(function() {
        $.ajax({
          url: 'bd.php',
          method: 'GET',
          dataType: 'json', 
          success: function(data) {
            $('#table-body').empty();

            data.forEach(function(row) {
              var newRow = "<tr>" +
                "<td>" + row.id + "</td>" +
                "<td>" + row.email + "</td>" +
                "<td>" + row.externalreference + "</td>" +
                "<td>" + row.valor + "</td>" +
                "<td>" + row.status + "</td>" +
                "<td><button class='btn btn-primary btn-sm edit-btn' data-id='" + row.id + "' data-email='" + row.email + "' data-externalreference='" + row.externalreference + "' data-valor='" + row.valor + "' data-status='" + row.status + "'>Editar</button></td>" +
                "</tr>";
              $('#table-body').append(newRow);
            });

            $('#user-table').DataTable({
              ordering: false, 
              columnDefs: [{
                orderable: false,
                targets: 5
              }] 
            });
          },
          error: function() {
            console.log('Erro ao obter dados do servidor.');
          }
        });
      });
    </script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
  </div>
  </div>
  <script src="../assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js"></script>
  <script src="../assets/extra-libs/sparkline/sparkline.js"></script>
  <script src="../dist/js/waves.js"></script>
  <script src="../dist/js/sidebarmenu.js"></script>
  <script src="../dist/js/custom.min.js"></script>
  <script src="../assets/extra-libs/multicheck/datatable-checkbox-init.js"></script>
  <script src="../assets/extra-libs/multicheck/jquery.multicheck.js"></script>
  <script src="../assets/extra-libs/DataTables/datatables.min.js"></script>
  <script>
    $("#zero_config").DataTable();
  </script>

  <script>
    $(document).on('click', '.edit-btn', function() {
      var saqueId = $(this).data('id');
      var saqueEmail = $(this).data('email');
      var saqueExternalReference = $(this).data('externalreference');
      var saqueValor = $(this).data('valor');
      var saqueStatus = $(this).data('status');

      $('#editId').val(saqueId);
      $('#editEmail').val(saqueEmail);
      $('#editExternalReference').val(saqueExternalReference); 
      $('#editValor').val(saqueValor);
      $('#editStatus').val(saqueStatus);

      $('#editModal').modal('show');
    });

    $('#editForm').submit(function(event) {
      event.preventDefault();

      var formData = $(this).serialize();

      $.ajax({
        url: 'update.php', 
        method: 'POST',
        data: formData,
        success: function(response) {
          $('#editModal').modal('hide');
        },
        error: function() {
          console.log('Erro ao enviar dados para o servidor.');
        }
      });
    });
  </script>
</body>

</html>