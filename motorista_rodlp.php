
<?php
session_start();
include("php/abreconexao.php");
include("php/verificalogin.php");
$idatual = $_SESSION['idusuario'];
$acessoAnterior = $_SESSION['acesso_anterior'];
$dispositivoAnterior = $_SESSION['dispositivo_anterior'];
$sqlatual = "SELECT u.status_2 as status_u, u.administrador, r.status_2 as status_r, u.ultimo_acesso, u.ip  FROM tbusuario u join tbregiao r on r.idregiao = u.tbregiao_idregiao WHERE u.idusuario = $idatual";
$resultatual = mysqli_query($conn, $sqlatual);
$rowsatual = mysqli_fetch_object($resultatual);
$status = $rowsatual -> status_u;
$dispositivo =  $rowsatual -> ip;
#$ultimoacesso = $rowsatual -> ultimo_acesso;
#$databr = substr($ultimoacesso,8,2).'/'.substr($ultimoacesso,5,2).'/'.substr($ultimoacesso,0,4);
$databr = $acessoAnterior;


$statusRegiao = $rowsatual -> status_r;
$administrador = $rowsatual -> administrador;
if($status != 'Ativo')
{
	header("Location: index.php");
	exit;
}
if($statusRegiao == 'Inativo' && $administrador == '0' )
{
	header("Location: index.php");
	exit;
}



// ---   guarda variáveis do usuario e região para mostrar no  cabeçalho --- //
$sqlreg = "SELECT u.status_2, r.nome as nome_r, u.nome as nome_u , r.mapa, u.thema as thema_u , r.idregiao as idregiao_r FROM  tbusuario u join tbregiao r on u.tbregiao_idregiao = r.idregiao WHERE u.idusuario = $idatual";
$resultreg = mysqli_query($conn, $sqlreg);
$rowsreg = mysqli_fetch_object($resultreg);
$regiao = $rowsreg -> nome_r;
$mapa = $rowsreg -> mapa;
$usuario = $rowsreg -> nome_u;
//$usuario = strtoupper($usuario);
$usuario = ucfirst($usuario);
$thema = $rowsreg -> thema_u;
$idregiao = $rowsreg -> idregiao_r;


$sql3 = "SELECT  * FROM tblmotorista  where tbregiao_idregiao = $idregiao order by nome ";
$result3 = mysqli_query($conn, $sql3);

    // if query results contains rows then featch those rows 
    if(mysqli_num_rows($result3) > 0)
    {
    	$number = 1;
    	while($row3 = mysqli_fetch_assoc($result3))
    	{
			$databr = substr($row3['dt_nasc'],8,2).'/'.substr($row3['dt_nasc'],5,2).'/'.substr($row3['dt_nasc'],0,4);
			$data3 .= '<tr>
				<td>'.$number.'</td>
				<td>'.$row3['nome'].'</td>
				<td>'.$row3['apelido'].'</td>
				<td>'.$databr.'</td>
				<td>'.$row3['celular'].'</td>';
				
				if($row3['status_2'] == 'Ativo')
				{
					$data3.='<td align="center"><a  href="upstatus_motorista_rodlp.php?id='.$row3['idmotorista'].'&pag='.$idpag.'"><span class="ico-eye" ></span></a></td>';
				} 
				else 
				{
					$data3.='<td align="center"><a   href="upstatus_motorista_rodlp.php?id='.$row3['idmotorista'].'&pag='.$idpag.'"><font color="lightgray"><span class="ico-eye-blocked" ></span></font></a></td>';
				}
				
				$data3.='<td align="center"> <a href="edita_motorista_rodlp.php?id='.$row3['idmotorista'].'"><span class="ico-pencil"></span></a></td>';

				
				
				// integridade dos dados: verifica se este registro existe numa outra tabela e se pode excluir.
				$sql4 = "SELECT  *  FROM tblmotorista as m WHERE m.tbregiao_idregiao  =  ".$row3['idregiao']." " ;
				$result4 = mysqli_query($conn, $sql4);
				$num = 0;
				while($row4 = mysqli_fetch_assoc($result4)) // verifica na tabela de usuarios
				{
					$num++;
				}
					$sql5 = "SELECT  *  FROM tblcaminhao_has_tblmotorista as cm WHERE cm.tblmotorista_idmotorista  =  ".$row3['idmotorista']." " ;
					$result5 = mysqli_query($conn, $sql5);
					
				while($row5 = mysqli_fetch_assoc($result5)) // verifica na tabela de caminhao_has_motorista
				{
					$num++;
				}

				
				
				if($num >  0)
				{
					$data3.='<td align="center"> <a data-toggle="modal" href="#myModalx" <font color="gray"><span class="ico-close"></span></font></a></td>';					
				}
				else
				{
					//$data3.='<td align="center"> <a data-toggle="modal" href="#myModalx">	<font color="red"><span class="ico-close"></span></font></a></td>';
					$data3.='<td align="center"> <a data-toggle="modal" href="deleta_motorista_rodlp.php?id='.$row3['idmotorista'].'">	<font color="red"><span class="ico-close"></span></font></a></td>';
				}
				
				
				
				
				
    		$data3.='</tr>';
    		$number++;
    	}
		//<button onclick="GetUserDetails('.$row3['idregiao'].')" class="btn btn-warning">Update</button>
    }
    else
    {
    	// records now found 
    	$data3.= '<tr><td colspan="6">Nenhum registro encontrado!</td></tr>';
    }


if($_SERVER['REQUEST_METHOD'] == 'POST' and !empty($_POST))
{
	
	$nome = $_POST['fnome'];
	$apelido = $_POST['fapelido'];
	$dt_nasc = $_POST['fdtnasc'];
	$documento = $_POST['fdocumento'];
	$telefone = $_POST['ftelefone'];
	$celular = $_POST['fcelular'];

	
	//$senha = $_POST['fsenha1'];

	$erroCNT = 0;
	$erroMSG = "";

	
	if ($erroCNT == 0)
	{	
		
		$sql = "INSERT INTO tblmotorista (nome, apelido, status_2, tbregiao_idregiao, dt_nasc, documento, telefone, celular) VALUES ('$nome','$apelido', 'Ativo', ".$idregiao.", '$dt_nasc', '$documento' , '$telefone','$celular')";
		mysqli_query($conn, $sql) or die(mysqli_error());
		header ("location: motorista_rodlp.php");
	}
	if ($erroCNT == 1)
	{
		$erroMSG = "Ocorreu o seguinte erro: \\n" .$erroMSG;
	}
	if ($erroCNT > 1)
	{
		$erroMSG = "Ocorreram os seguintes erros: \\n" .$erroMSG;
	}
}

// ---   guarda variáveis do usuario e região para mostrar no  cabeçalho --- //
$sqlreg = "SELECT u.status_2, r.nome as nome_r, u.nome as nome_u FROM  tbusuario u join tbregiao r on u.tbregiao_idregiao = r.idregiao WHERE u.idusuario = $idatual";
$resultreg = mysqli_query($conn, $sqlreg);
$rowsreg = mysqli_fetch_object($resultreg);
$regiao = $rowsreg -> nome_r;
$usuario = $rowsreg -> nome_u;
//$usuario = strtoupper($usuario);
$usuario = ucfirst($usuario);
include("../php/fechaconexao.php");

?>

<!DOCTYPE html>
<html lang="pt-br" class="<?php echo $thema;?> ">
	<head>
	<title>Rodolapa</title>
  <meta charset="utf-8">
 
  <!-- Isso é necessário para funcionar a versão mobile -->
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
 
  <!-- CSS -->
  <link rel="stylesheet" type="text/css" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="//assets.locaweb.com.br/locastyle/2.0.6/stylesheets/locastyle.css">
 
</head>
<body>
 
 <?php include('cabecalho_rodlp.php') ?>
  <!-- Aqui começa a parte de conteúdo dividido por colunas -->

  <main class="main">
    <div class="container">
      <div class="row">
		<div class="col-md-2 content" role="main"></div>
        <div class="col-md-8 content" role="main">
          <br><br>
			 
			<h3 class="ico-truck"> <strong> Cadastro de Motorista</strong></h3> <br>

            			<form role="form" id="foxrmContato" tabindex="1" action="<?php $_SERVER['SCRIPT_NAME'] ?>" method="post">
						
							<div class="row">
								<div class="form-group col-lg-7">
									<input type="text" class="form-control" placeholder="Nome do motorista"  name="fnome" required="" >  
								</div>
								<div class="form-group col-lg-5">
									<input type="text" class="form-control" placeholder="apelido"  name="fapelido" required="" > 
								</div>
							</div>	
							<label >Data de Nasc:  </label>
							<div class="row">
								<div class="form-group col-lg-6">
										<input type="date" class="form-control" name="fdtnasc"  placeholder="dd/mm/aaaa"  required="">	
								</div>
								<div class="form-group col-lg-6">
									<input type="text" class="form-control"   name="fdocumento" placeholder="documento" required="" >
								</div>
							</div>	
							<div class="row">
								<div class="form-group col-lg-6">
									<input id="telefone" name="ftelefone" type="telefone" placeholder="Telefone" data-mask="(99) 9999-9999" class="form-control" > 
								</div>
								<div class="form-group col-lg-6">
									<input id="celular" name="fcelular" type="celular" placeholder="Celular" data-mask="(99) 9 9999-9999" maxlength="16" autocomplete="off" class="form-control" > 
								</div>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
								<button  class="btn btn-primary" type="submit">Salvar</button>
							</div>
								
								
						</form>
							
			
			
			<br>
			<table align="center" class="table table-bordered table-striped">
						<tr>
							<th>No.</th>
							<th>Nome</th>
							<th>Apelido</th>
							<th>Data_nasc</th>
							<th>Celular</th>
							<th>Status</th>
							<th>Editar</th>
							<th>Deletar</th>
						</tr>
				<?php echo $data3; ?>
			</table>



        </div>
		<div class="col-md-2 content" role="main"></div>
      </div>
    </div>
  </main>		

  
  				<!-- Modal -->
				<div class="modal fade" id="myModalx" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
								<h4 class="modal-title" id="myModalLabel">Exclusão de registro</h4>
							</div>
							<div class="modal-body">
									<div class="alert alert-danger"> <h4><strong>ATENÇÃO:</strong></h4>Você não pode excluir este registro pois este motorista já está vinculado a um ou mais caminhões !</div>
									<div class="alert alert-warning"><h4 class="ico-pencil"><strong>Dica!</strong> </h4> Utilize a coluna status (opção <span class="ico-eye-blocked"></span>), para ocultar este cliente de futuras ações. </div>		
							</div>
							
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
							</div>
						</div><!-- /.modal-content -->
					</div><!-- /.modal-dialog -->
				</div><!-- /.modal -->
							
  
  
  <!-- Footer -->
  <footer class="footer">
    <div class="footer-menu">
      <nav class="container">
        <h2 class="title-footer">suporte e ajuda</h2>
        <ul class="no-liststyle">
          <li><a href="#" class="bg-customer-support"><span class="visible-lg">Atendimento</span></a></li>
          <li><a href="#" class="bg-my-tickets"><span class="visible-lg">Meus Chamados</span></a></li>
          <li><a href="#" class="bg-help-desk"><span class="visible-lg">Central de Ajuda (Wiki)</span></a></li>
          <li><a href="#" class="bg-statusblog"><span class="visible-lg">Statusblog</span></a></li>
        </ul>
      </nav>
    </div>
    <div class="container footer-info">
      <span class="last-access ico-screen"><strong>Último acesso: </strong>7/8/2011 22:35:49</span>
      <div class="set-ip"><span class="set-ip"><strong>IP:</strong> 201.87.65.217</span></div>
      <p class="copy-right">RodoLapa Ltda - ME.</p>
    </div>
  </footer>
 
  <!-- Scripts - Atente-se na ordem das chamadas -->
  <script type="text/javascript" src="//code.jquery.com/jquery-2.0.3.min.js"></script>
  <script type="text/javascript" src="//assets.locaweb.com.br/locastyle/2.0.6/javascripts/locastyle.js"></script>
  <script type="text/javascript" src="//netdna.bootstrapcdn.com/bootstrap/3.0.3/js/bootstrap.min.js"></script>
</body>
</html>
