
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

$idvendat = $_GET['idv'];

/*$pos = strpos($idprod, '-');	
	if ($pos > 0)
	{
		$idprod = str_replace(' ','',$idprod);
	}
	*/
	
// verifica quais itens estão neste orçamento

$sql3 = "SELECT  p.codigoproduto ,p.nome, p.fabricante, t.quantidade, t.preco, t.subtotal, t.iditens_vendat FROM tblitens_venda_t t join tblproduto p on p.idproduto = t.idproduto where tblitens_idvendat =  $idvendat  ";
$result3 = mysqli_query($conn, $sql3);

    // if query results contains rows then featch those rows 
    if(mysqli_num_rows($result3) > 0)
    {
    	$number = 1;
		$total = 0;
    	while($row3 = mysqli_fetch_assoc($result3))
    	{
			////$databr = substr($row3['dt_nasc'],8,2).'/'.substr($row3['dt_nasc'],5,2).'/'.substr($row3['dt_nasc'],0,4);
			$precoF = number_format($row3['preco'], 2, ',', '.');
			$subtotalF = number_format($row3['subtotal'], 2, ',', '.');
			$data3 .= '<tr>
				<td>'.$number.'</td>
				<td>'.$row3['codigoproduto'].'</td>
				<td>'.$row3['nome'].'</td>
				<td>'.$row3['fabricante'].'</td>
				<td>'.$row3['quantidade'].'</td>
				<td>'.$precoF.'</td>				
				<td>'.$subtotalF.'</td>';
				//$data3.='<td align="center"> <a href="edita_quant_orcamento_rodlp.php?id='.$row3['idmotorista'].'"><span class="ico-pencil"></span></a></td>';
				$data3.='<td align="center"> <a data-toggle="modal" href="deleta_item_orcamento_rodlp.php?id='.$row3['iditens_vendat'].'&idv='.$_GET['idv'].'">	<font color="red"><span class="ico-close"></span></font></a></td>';
				$total = $total + $row3['subtotal'];
				$total_atualizado = $total_atualizado + ($row3['preco_venda'] * $row3['quantidade']);
				
    		$data3.='</tr>';
    		$number++;
    	}
		$diferenca =  $total_atualizado - $total ;
		$total = number_format($total, 2, ',', '.');

		//<button onclick="GetUserDetails('.$row3['idregiao'].')" class="btn btn-warning">Update</button>
    }
    else
    {
    	// records now found 
    	$data3.= '<tr><td colspan="6">Nenhum registro encontrado!</td></tr>';
    }



	
	
	
if($_SERVER['REQUEST_METHOD'] == 'POST' and !empty($_POST))
{
	
  
	$idprod = '';
	$idprod = $_POST['prod'];
	$quant_atual = $_POST['fquant_atual'];
	
  if ($idprod <> '')
  {	

	$sql_quant = "Select count(*) as quant from tblproduto where codigoproduto = '".$idprod."' and tbregiao_idregiao = $idregiao ";
	$result_quant = mysqli_query($conn, $sql_quant);
	$rows_quant = mysqli_fetch_object($result_quant);
	$quant = $rows_quant -> quant;
	
	
	
	if($quant == 1) // achou o produto pelo código
	{
		$sql_dado = "Select idproduto, preco_venda from tblproduto where codigoproduto = '".$idprod."' and tbregiao_idregiao = $idregiao";
		$result_dado = mysqli_query($conn, $sql_dado);
		$rows_dado = mysqli_fetch_object($result_dado);
		$codigo_selecionado = $rows_dado -> idproduto;
		$pvenda = $rows_dado -> preco_venda;
		$subtotal = $pvenda * $quant_atual;
		
		// agora faz o insert no orçamento
		
		// verifica se o códido do produto já existe neste orçamento...
		
		$sql_ja_existe = "Select quantidade, count(*) as contj from tblitens_venda_t where tblitens_idvendat = ".$idvendat." and idproduto = ".$codigo_selecionado." group by quantidade limit 1";
		$result_ja_existe = mysqli_query($conn, $sql_ja_existe);
		$rows_ja_existe = mysqli_fetch_object($result_ja_existe);
		$quantj = $rows_ja_existe -> quantidade;
		$contj = $rows_ja_existe -> contj;
		
		if ($contj == 1 )	//caso encontre.... faz um update, caso não encontre faz um insert
		{
			$quantf = $quantj + $quant_atual;
			$subtotal = $pvenda * $quantf;
			$sql_update = "Update tblitens_venda_t set quantidade = ".$quantf." , subtotal = ".$subtotal." where tblitens_idvendat = ".$idvendat." and idproduto = ".$codigo_selecionado."  ";
			mysqli_query($conn, $sql_update) or die(mysqli_error());
		}
		else // agora faz o insert no orçamento
		{
			$sql_insert = "Insert into tblitens_venda_t ( tblitens_idvendat, idproduto, quantidade, preco, subtotal) 
									values ( $idvendat ,$codigo_selecionado, $quant_atual, $pvenda, $subtotal)";
			mysqli_query($conn, $sql_insert) or die(mysqli_error());
		}
		header ("location: orcamento_rodlp.php?idv=".$_GET['idv']." ");

		
		// recarrega a mesma página

		
	}
	else
		if($quant > 1) // achou mais de um produto com o mesmo código de barras
		{
			// deve criar uma lista de produtos e mandar no modal para selecionar
			header ("location: orcamento_rodlp.php?idv=".$_GET['idv']."&p=1&cod=".$idprod." ");
			

		}
		else // não achou o produto.... agora deve dar um like pelo nome do produto
		{
			
			header ("location: orcamento_rodlp.php?idv=".$_GET['idv']."&p=1&cod=".$idprod." ");
			
			
		}
//$usuario = $rowsdados -> nome_u;
  }
}



	
include("../php/fechaconexao.php");

?>


<!DOCTYPE html>
<html lang="pt-br" class="<?php echo $thema;?> ">
	<head>
	<title>Rodolapa - Orçamentos</title> 
  <meta charset="utf-8">
 
  <!-- Isso é necessário para funcionar a versão mobile -->
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
 
  <!-- CSS -->
  <link rel="stylesheet" type="text/css" href="//netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="//assets.locaweb.com.br/locastyle/2.0.6/stylesheets/locastyle.css">
 
</head>
<body>
 
 
  <!-- Header principal -->
  <!--<header class="header" role="banner">
  -->
  <br>
    <div class="container">
      <span class="control-menu visible-xs ico-menu-2"></span>
      <span class="control-sidebar visible-xs ico-list"></span>
	  <div class="input-group col-md-2 f-left">
		<div class="btn-group">
			<a class="btn btn-primary ico-menu" href="#">Menu</a>
			<a class="btn btn-primary dropdown-toggle" data-toggle="dropdown" href="#">
 
			</a>
			<ul class="dropdown-menu">
				<li><a href="orcamento_salvar_rodlp.php?idv=<?php $orca =  $_GET['idv']; echo $orca;?>"><i class="icon-pencil"></i> Salvar orçamento</a></li>
				<li><a href="orcamento_temporario_pdf_rodlp.php?idv=<?php $orca =  $_GET['idv']; echo $orca;?>" target="_blank"><i class="icon-trash"></i> Gerar um pdf/imprimir</a></li>
				<li class="divider"></li>
				<li><a href="principal_rodlp.php"><i class="i"></i> Sair</a></li>
			</ul>
			
		</div>
	</div>
	  
	  
		
		
		<div class="input-group col-md-6 f-left">
		 <h4 class="project-name"><a href="#">Orçamentos (Modo rascunho)</a></h4> 	
		<!--<font size="6">	RodoLapa - Orçamentos </font>-->  
		<br>

   			<form role="form" id="foxrmContato" tabindex="1" action="<?php $_SERVER['SCRIPT_NAME'] ?>" method="post">
						
<!--			<form id="formPesq" tabindex="1" >
-->
				
					<div class="form-group col-lg-2">
						<input type="number" class="form-control" min="1.00" max="9999.00" step="1"  value="1" id="fquant_atual" name="fquant_atual" tabindex="3">
					</div>
					<div class="input-group col-md-10 f-right" >
					
					<input type="text"  class="form-control" placeholder="Código de barras ou nome do produto e ENTER" id="prod" name="prod" >
					<span class="input-group-btn" type="submit" >
						<button  class="btn btn-primary" type="submit" ><span class="ico-search"></span></button>
					<!--<button  class="btn btn-success" type="submit">Salvar</button> -->
					</span>
			</div>
			</form>
		</div>
		<div class="input-group col-md-1 f-left">
		
		</div>
		<div class="input-group col-md-3 f-left">
				<br><b>Data:</b> 	<?php 
											
											$h = "3"; //HORAS DO FUSO ((BRASÍLIA = -3) COLOCA-SE SEM O SINAL -).
											$hm = $h * 60;
											$ms = $hm * 60;
											//COLOCA-SE O SINAL DO FUSO ((BRASÍLIA = -3) SINAL -) ANTES DO ($ms). DATA
											$gmdata = gmdate("m/d/Y", time()-($ms)); 
											//COLOCA-SE O SINAL DO FUSO ((BRASÍLIA = -3) SINAL -) ANTES DO ($ms). HORA
											$gmhora = gmdate("g:i", time()-($ms)); 


											$Dt_cod = $gmdata." ".$gmhora;	
											
											echo $Dt_cod;	
									?>
				<br><b>Usuário:</b> <?php echo $usuario; ?>
				
						
				
		</div>
  <!--    <a href="#" class="help-suggestions ico-question hidden-xs">Ajuda e Sugestões</a> -->
    </div>
  <!--</header> -->
 
  <!-- Menu -->
  <div class="nav-content">
    <h2 class="title-sep visible-xs">Mais</h2>
    <ul class="nav-mob-list visible-xs">
      <li><a href="#" class="ico-help-circle">Ajuda e sugestões</a></li>
    </ul>
  </div>
 
  <!-- Aqui começa a parte de conteúdo dividido por colunas -->
  <main class="main">
    <div class="container">
      <div class="row">
        <div class="col-md-8 content" role="main">
				
				<table class="table ls-table">
					<tr>
	<!--					<th class="txt-center"><input type="checkbox"></th>
	-->
						<th>No.</th>
						<th>Cód Produto</th>
						<th>Produto</th>						
						<th>Fabricante</th>
						<th>Quant.</th>
						<th>Preço</th>					
						<th>Subtotal</th>
						<th>Deletar</th>
							
					</tr>
					<?php echo $data3; ?>
					<tr>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th>Total: </th>
						<th><?php echo 'R$ '.$total; ?></th>
						<th></th>
						
				
				</table>
				<br>
					<div class="pagination-filter">
					 
						<ul class="pagination">
							<li><a href="orcamento_rodlp.php?pag=<?php echo $pgA;?>">« Anterior</a></li>
							<?php echo $li_pg1; ?><a href="orcamento_rodlp.php?pag=<?php echo $pg1;?>"><?php echo $pg1;?></a></li>
							<?php echo $li_pg2; ?><a href="orcamento_rodlp.php?pag=<?php echo $pg2;?>"><?php echo $pg2;?></a></li>
							<?php echo $li_pg3; ?><a href="orcamento_rodlp.php?pag=<?php echo $pg3;?>"><?php echo $pg3;?></a></li>
							<?php echo $li_pg4; ?><a href="orcamento_rodlp.php?pag=<?php echo $pg4;?>"><?php echo $pg4;?></a></li>
							<?php echo $li_pg5; ?><a href="orcamento_rodlp.php?pag=<?php echo $pg5;?>"><?php echo $pg5;?></a></li>
							<li><a href="orcamento_rodlp.php?pag=<?php echo $pgP;?>">Próximo »</a></li>
						</ul>

					 
					</div>
		  

		  <br>

      </div>
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
      
	  
	  
	  					 Último acesso: 
						<?php if ($dispositivoAnterior == 'pc' ){?>
							<span class="ico-screen"> </span> 
						<?php } else { ?>
							<span class="ico-mobile">  </span> 
						<?php } echo $databr; ?>
					 
	  
	  
	  
	  
      <div class="set-ip"><span class="set-ip"></span></div>
      <p class="copy-right">RodoLapa Ltda - ME.</p>
    </div>
  </footer>
 
  <!-- Scripts - Atente-se na ordem das chamadas -->
  <script type="text/javascript" src="//code.jquery.com/jquery-2.0.3.min.js"></script>
  <script type="text/javascript" src="//assets.locaweb.com.br/locastyle/2.0.6/javascripts/locastyle.js"></script>
  <script type="text/javascript" src="//netdna.bootstrapcdn.com/bootstrap/3.0.3/js/bootstrap.min.js"></script>
  
  <?php
  
// ---   guarda variáveis do usuario e região para mostrar no  cabeçalho --- //
$sqlreg = "SELECT u.status_2, r.nome as nome_r, u.nome as nome_u , r.mapa, u.thema as thema_u , r.idregiao as idregiao_r FROM  tbusuario u join tbregiao r on u.tbregiao_idregiao = r.idregiao WHERE u.idusuario = $idatual";
$resultreg = mysqli_query($conn, $sqlreg);
$rowsreg = mysqli_fetch_object($resultreg);
$idregiao = $rowsreg -> idregiao_r;
  
  
	$idpainel = $_GET['p'];
	$idprod = $_GET['cod'];


	
	if( $idpainel == '1')
	{

		$sql33 = "SELECT  p.codigoproduto, p.nome, p.fabricante, p.idproduto, p.preco_venda, p.foto
				FROM tblproduto p
				WHERE p.tbregiao_idregiao  =  '".$idregiao."'  and ( (p.nome like '%".$idprod."%') OR (p.codigoproduto like '%".$idprod."%')) ";

		$result33 = mysqli_query($conn, $sql33);
		// if query results contains rows then featch those rows 
		if(mysqli_num_rows($result33) > 0)
		{
			$numberx = 1;
			while($row33 = mysqli_fetch_assoc($result33))
			{
				
				$data33 .= '<tr>
					<td>'.$number.'</td>  
					<td>'.$row33['codigoproduto'].'</td>
					<td>'.$row33['nome'].'</td>
					<td>'.$row33['fabricante'].'</td>
					<td>'.$row33['preco_venda'].'</td>';
					
					$data33.='<td align="center"> <a data-toggle="modal" href="#">	<font color="gray"><span class="ico-checkmark" onclick="calcValor(\''.$row33['codigoproduto'].'\')"></span></font></a></td>';
					$data33.='<td align="center"> <img src="'.$row33['foto'].'" height="50" width="auto"></td>';
					
					
				$data33.='</tr>';
				$number++;
			}
			//<button onclick="GetUserDetails('.$row3['idregiao'].')" class="btn btn-warning">Update</button>
		}
		else
		{
			// records now found 
			$data33.= '<tr><td colspan="6">Nenhum registro encontrado!</td></tr>';
		}
	}	
?>
 
 
 				<!-- Modal -->
				<div class="modal fade" id="myModal_escolhe_produto" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
								<h4 class="modal-title" id="myModalLabel">Selecione um produto</h4>
							</div>
							<div class="modal-body">
									<div class="alert alert-success"> <h4><strong>Dados:</strong></h4> 
										<table class="table ls-table">
											<tr>
												
												<th>No.</th>
												<th>Codigo do Produto</th>
												<th>Produto</th>
												<th>Fabricante</th>
												<th>Preço</th>
												<th>Selecionar</th>
												<th>Imagem do produto</th>
													
											</tr>
											<?php echo $data33; ?>
												
										
										</table>
									
									</div>
									
									

									
							</div>
							
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Fechar</button>
							</div>
						</div><!-- /.modal-content -->
					</div><!-- /.modal-dialog -->
				</div><!-- /.modal -->

<?php
	

	$idpainel = $_GET['p'];	
	if( $idpainel == '1' )
	{
		?>	
			<script>
				$('#myModal_escolhe_produto').modal('show');
			</script>
		<?php   
		
	}
 ?>
  
  
  		 
 
  
</body>
</html>

<script type="text/javascript">

document.getElementById("prod").focus();

function calcValor(codstring){
	
     document.getElementById("prod").value = codstring ;
	 $('#myModal_escolhe_produto').modal('hide');
	 document.getElementById("prod").focus();
}



</script>