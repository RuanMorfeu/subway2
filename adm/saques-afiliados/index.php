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
  <!-- Custom CSS -->
  <link href="../assets/libs/flot/css/float-chart.css" rel="stylesheet" />
  <!-- Custom CSS -->
  <link href="../dist/css/style.min.css" rel="stylesheet" />


  <?php
/*   ini_set('display_errors', 1);
  ini_set('display_startup_erros', 1);
  error_reporting(E_ALL); */
  // Conectar ao banco de dados
  include './../../conectarbanco.php';

  $conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);

  // Verificar a conexão
  if ($conn->connect_error) {
    die("Erro na conexão com o banco de dados: " . $conn->connect_error);
  }

  // Obtém as credenciais do gateway
  $client_id = '';
  $client_secret = '';

  $sql = "SELECT client_id, client_secret FROM gateway";
  $result = $conn->query($sql);
  if ($result) {
    $row = $result->fetch_assoc();
    if ($row) {
      $client_id = $row['client_id'];
      $client_secret = $row['client_secret'];
    }
  } else {
    // Tratar caso ocorra um erro na consulta
  }

  $conn->close();
  ?>



</head>

<body>
  <!-- ============================================================== -->
  <!-- Preloader - style you can find in spinners.css -->
  <!-- ============================================================== -->

  <!-- ============================================================== -->
  <!-- Main wrapper - style you can find in pages.scss -->
  <!-- ============================================================== -->
  <div id="main-wrapper" data-layout="vertical" data-navbarbg="skin5" data-sidebartype="full"
    data-sidebar-position="absolute" data-header-position="absolute" data-boxed-layout="full">
    <!-- ============================================================== -->
    <!-- Topbar header - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <header class="topbar" data-navbarbg="skin5">
      <nav class="navbar top-navbar navbar-expand-md navbar-dark">
        <div class="navbar-header" data-logobg="skin5">
          <!-- ============================================================== -->
          <!-- Logo -->
          <!-- ============================================================== -->
          <a class="navbar-brand" href="../">
            <!-- Logo icon -->
            <b class="logo-icon ps-2">
              <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
              <!-- Dark Logo icon -->
              <img src="../assets/images/logo-icon.png " alt="homepage" class="light-logo" width="25" />
            </b>
            <!--End Logo icon -->
            <!-- Logo text -->
            <span class="logo-text ms-2">
              <!-- dark Logo text -->
                 <!-- <img
                  src="../assets/images/logo-text.webp"
                  width="150" height="50"
                  alt="homepage"
                  class="light-logo"
                /> -->
            </span>

          </a>

          <a class="nav-toggler waves-effect waves-light d-block d-md-none" href="javascript:void(0)"><i
              class="ti-menu ti-close"></i></a>
        </div>

        <div class="navbar-collapse collapse" id="navbarSupportedContent" data-navbarbg="skin5">

          <ul class="navbar-nav float-start me-auto">
            <li class="nav-item d-none d-lg-block">
              <a class="nav-link sidebartoggler waves-effect waves-light" href="javascript:void(0)"
                data-sidebartype="mini-sidebar"><i class="mdi mdi-menu font-24"></i></a>
            </li>



          </ul>
        </div>
      </nav>
    </header>
   
    
    <?php
    // Conectar ao banco de dados
    include './../../conectarbanco.php';
    
    $conn = new mysqli('localhost', $config['db_user'], $config['db_pass'], $config['db_name']);
    
    // Verificar a conexão
    if ($conn->connect_error) {
        die("Erro na conexão com o banco de dados: " . $conn->connect_error);
    }
    
    // Obtém as credenciais do gateway
    $client_id = '';
    $client_secret = '';
    
    $sql = "SELECT client_id, client_secret FROM gateway";
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        if ($row) {
            $client_id = $row['client_id'];
            $client_secret = $row['client_secret'];
        }
    } else {
        // Tratar caso ocorra um erro na consulta
    }
    
    $conn->close();
    
    ?>
    
    
    
    

    
    <?php include '../components/aside.php' ?>
   
      <div class="page-wrapper">
  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Tabela de Saques</h5>
      <div class="table-responsive">
        <h5>Filtrar por status</h5>
        <select id="selectedStatus">
            <option value="">Todos</option>
            <option value="PAID_OUT">Aprovado</option>
            <option value="WAITING_FOR_APPROVAL">Pendente</option>
        </select>
        <table id="user-table" class="table table-striped table-bordered">
          <thead>
            <tr>
                <th>ID</th>
                <th>Email</th>
                <th>Nome</th>
                <th>PIX</th>
                <th>Valor</th>
                <th>Status</th>
                <th>Ações</th>
            </tr>
          </thead>
          <tbody id="table-body">
            <!-- Dados da tabela serão inseridos aqui -->
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Detalhes -->
    <div class="modal fade" id="modalDetalhes" tabindex="-1" role="dialog" aria-labelledby="modalDetalhesLabel"
      aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalDetalhesLabel">Confirmar Saque de Afiliado</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
          <p><strong>id:</strong> <span id="detalheId"></span></p>
            <p><strong>Email:</strong> <span id="detalheEmail"></span></p>
            <p><strong>Nome:</strong> <span id="detalheNome"></span></p>
            <p><strong>Pix:</strong> <span id="detalhePix"></span></p>
            <p><strong>Valor:</strong> <span id="detalheValor"></span></p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal" id="btnFechar">CANCELAR</button>
            <button type="button" class="btn btn-danger" id="btnConfirmar">CONFIRMAR</button>
          </div>
        </div>
=======
<!-- Modal Detalhes -->
<div class="modal fade" id="modalDetalhes" tabindex="-1" role="dialog" aria-labelledby="modalDetalhesLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalDetalhesLabel">Confirmar Saque de Afiliado</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p><strong>id:</strong> <span id="detalheId"></span></p>
        <p><strong>Email:</strong> <span id="detalheEmail"></span></p>
        <p><strong>Nome:</strong> <span id="detalheNome"></span></p>
        <p><strong>Pix:</strong> <span id="detalhePix"></span></p>
        <p><strong>Valor:</strong> <span id="detalheValor"></span></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" id="btnFechar">CANCELAR</button>
        <button type="button" class="btn btn-danger" id="btnConfirmar">CONFIRMAR</button>
      </div>
    </div>
  </div>
</div>

<script>
    
</script>


<script>
  var selectedId
  $(document).ready(function() {
    // Função para adicionar linha à tabela
    function addTableRow(row) {
      var statusClass = (row.status === 'Aguardando Aprovação') ? 'text-danger' : 'text-success';

      var newRow = `<tr>
        <td>${row.id}</td>
        <td>${row.email}</td>
        <td>${row.nome}</td>
        <td>${row.pix}</td>
        <td>${row.valor}</td>
        <td class='${statusClass}'>${row.status}</td>
        <td>`;

      if (row.status === 'Aguardando Aprovação') {
        newRow += `<button class='btn-aprovar' data-toggle='modal' data-target='#modalDetalhes' 
                      data-id='${row.id}' data-email='${row.email}' data-nome='${row.nome}' data-pix='${row.pix}' 
                      data-valor='${row.valor}'>Aprovar</button>`;
      }

      newRow += '</td></tr>';

      $('#table-body').append(newRow);
    }

    // Use AJAX para buscar dados do arquivo PHP
    $.ajax({
      url: 'bd.php',
      method: 'GET',
      success: function(data) {
        // Limpar o corpo da tabela
        $('#table-body').empty();

        // Inserir dados na tabela
        data.forEach(addTableRow);

        // Adicione um evento de clique para o botão Aprovar
        $(document).on('click', '.btn-aprovar', function() {
          var id = $(this).data('id');
          var email = $(this).data('email');
          var nome = $(this).data('nome');
          var pix = $(this).data('pix');
          var valor = $(this).data('valor');

          selectedId = id

          // Preencha os detalhes no modal
          $('#detalheId').text(id);
          $('#detalheEmail').text(email);
          $('#detalheNome').text(nome);
          $('#detalhePix').text(pix);
          $('#detalheValor').text(valor);

          // Exiba o modal
          $('#modalDetalhes').modal('show');
        });

        $('#btnConfirmar').on('click', function() {
            // var id = $(this).data('id');
            console.log(selectedId);
            var requestData = {
                "id": selectedId
            };

            $.ajax({
    type: "GET", // Mudando de POST para GET
    url: "approve.php?id=" + requestData.id, // Adicionando o ID como parâmetro na URL
    contentType: "application/json",
    success: function(response) {
        console.log('Saque aprovado:', response);
        $('#modalDetalhes').modal('hide');
        window.location.reload();
    },
    error: function(error) {
        console.error('Erro ao aprovar o saque:', error);
    }
});

        });

        $('#btnFechar').on('click', function() {
          $('#modalDetalhes').modal('hide');
        });

        $('#user-table').DataTable({
          ordering: false 
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







      
        <!-- ============================================================== -->
        <!-- End footer -->
        <!-- ============================================================== -->
      </div>
    </div>

    <script>

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
    $("#zero_config").DataTable({ordering: false });
  </script>
</body>

</html>