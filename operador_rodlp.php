
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


$sql3 = "SELECT  o.idoperador, o.nome, o.status_2, o.dt_nasc, r.nome as regiao, o.celular FROM tboperador o join tbregiao r on r.idregiao = o.tbregiao_idregiao  where o.tbregiao_idregiao = $idregiao order by o.nome ";
$result3 = mysqli_query($conn, $sql3);

    // if query results contains rows then featch those rows 
    if(mysqli_num_rows($result3) > 0)
    {
    	$number = 1;
    	while($row3 = mysqli_fetch_assoc($result3))
    	{
		$dataok = substr($row3['dt_nasc'],8,2).'/'.substr($row3['dt_nasc'],5,2).'/'.substr($row3['dt_nasc'],0,4);
			$data3 .= '<tr>
				<td>'.$number.'</td>
				<td>'.$row3['nome'].'</td>
				<td>'.$dataok.'</td>
				<td>'.$row3['celular'].'</td>';
				
				if($row3['status_2'] == 'Ativo')
				{
					$data3.='<td align="center"><a  href="upstatus_operador_rodlp.php?id='.$row3['idoperador'].'&pag='.$idpag.'"><span class="ico-eye" ></span></a></td>';
				} 
				else 
				{
					$data3.='<td align="center"><a   href="upstatus_operador_rodlp.php?id='.$row3['idoperador'].'&pag='.$idpag.'"><font color="lightgray"><span class="ico-eye-blocked" ></span></font></a></td>';
				}
				
				$data3.='<td align="center"> <a href="edita_operador_rodlp.php?id='.$row3['idoperador'].'"><span class="ico-pencil"></span></a></td>';

				
				/*
				// integridade dos dados: verifica se este registro existe numa outra tabela e se pode excluir.
				$sql4 = "SELECT  *  FROM tbusuario as u WHERE u.tbregiao_idregiao  =  ".$row3['idregiao']." " ;
				$result4 = mysqli_query($conn, $sql4);
				$num = 0;
				while($row4 = mysqli_fetch_assoc($result4)) // verifica na tabela de usuarios
				{
					$num++;
				}
					$sql5 = "SELECT  *  FROM tbcliente as c WHERE c.tbregiao_idregiao  =  ".$row3['idregiao']." " ;
					$result5 = mysqli_query($conn, $sql5);
					
				while($row5 = mysqli_fetch_assoc($result5)) // verifica na tabela de clientes
				{
					$num++;
				}

				
				
				if($num >  0)
				{
					$data3.='<td align="center"> <a data-toggle="modal" href="#myModalx" <font color="gray"><span class="ico-close"></span></font></a></td>';					
				}
				else
				{
				*/	//$data3.='<td align="center"> <a data-toggle="modal" href="#myModalx">	<font color="red"><span class="ico-close"></span></font></a></td>';
					$data3.='<td align="center"> <a data-toggle="modal" href="deleta_operador_rodlp.php?id='.$row3['idoperador'].'">	<font color="red"><span class="ico-close"></span></font></a></td>';
			//	}
				
				
				
				
				
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
	$dt_nasc = $_POST['fdtnasc'];
	$celular = $_POST['fcelular'];
	
	//$senha = $_POST['fsenha1'];

	$erroCNT = 0;
	$erroMSG = "";

	
	if ($erroCNT == 0)
	{	
		
		$sql = "INSERT INTO tboperador (nome, status_2, tbregiao_idregiao, dt_nasc, celular) VALUES ('$nome', 'Ativo', ".$idregiao.", '$dt_nasc', '$celular' )";
		mysqli_query($conn, $sql) or die(mysqli_error());
		header ("location: operador_rodlp.php");
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
			
			<h3> <img src="img\ico-ferramenta.png" alt="" height="24" width="24"><strong> Cadastro de Operador</strong></h3> <br>

            			<form role="form" id="foxrmContato" tabindex="1" action="<?php $_SERVER['SCRIPT_NAME'] ?>" method="post">
						
							<div class="row">
								<div class="form-group col-lg-2"></div>
							
								<div class="form-group col-lg-8">
									<div class="form-group col-lg-12">
										<label >Nome:  </label> <input type="text" class="form-control" placeholder="Nome do operador"  name="fnome" required="" > 
									</div>
									<div class="form-group col-lg-6">	
											<label >Data de Nasc:  </label>	<input type="date" class="form-control" name="fdtnasc" id="dtnasc" placeholder="dd/mm/aaaa"  required="">	
									</div>
									<div class="form-group col-lg-6">	
										<label >Celular:  </label><input id="celular" name="fcelular" type="celular" placeholder="Celular" data-mask="(99) 9 9999-9999" maxlength="16" autocomplete="off" class="form-control" > 
									</div>
								</div>
								<div class="form-group col-lg-2"></div>
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
