<!DOCTYPE html>
<html>
<com:THead >
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="vendor/almasaeed2010/adminlte/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="vendor/almasaeed2010/adminlte/bower_components/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="vendor/almasaeed2010/adminlte/bower_components/Ionicons/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="vendor/almasaeed2010/adminlte/dist/css/AdminLTE.min.css">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="vendor/almasaeed2010/adminlte/dist/css/skins/_all-skins.min.css">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->

  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
  
	<link rel="apple-touch-icon" sizes="57x57" href="favicon/apple-icon-57x57.png">
	<link rel="apple-touch-icon" sizes="60x60" href="favicon/apple-icon-60x60.png">
	<link rel="apple-touch-icon" sizes="72x72" href="favicon/apple-icon-72x72.png">
	<link rel="apple-touch-icon" sizes="76x76" href="favicon/apple-icon-76x76.png">
	<link rel="apple-touch-icon" sizes="114x114" href="favicon/apple-icon-114x114.png">
	<link rel="apple-touch-icon" sizes="120x120" href="favicon/apple-icon-120x120.png">
	<link rel="apple-touch-icon" sizes="144x144" href="favicon/apple-icon-144x144.png">
	<link rel="apple-touch-icon" sizes="152x152" href="favicon/apple-icon-152x152.png">
	<link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-icon-180x180.png">
	<link rel="icon" type="image/png" sizes="192x192"  href="favicon/android-icon-192x192.png">
	<link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="96x96" href="favicon/favicon-96x96.png">
	<link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
	<link rel="manifest" href="favicon/manifest.json">
	<meta name="msapplication-TileImage" content="favicon/ms-icon-144x144.png">
	
	
</com:THead>
<body class="hold-transition skin-blue sidebar-mini sidebar-collapse" >
    <com:TForm >
        <com:TClientScript PradoScripts="prado, ajax, effects, validator, bootstrap" />
        <com:TClientScript>
          var ide = "";
        </com:TClientScript>
        <div class="wrapper" >
            <header class="main-header">
                <!-- Logo -->
                <a href="." class="logo">
                    <com:TLabel id="lProyect" />
                </a>
                <!-- Header Navbar: style can be found in header.less -->
                <nav class="navbar navbar-static-top">
                    <!-- Sidebar toggle button-->
                    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
                        <span class="sr-only">Megatec</span>
                    </a>
                    <com:THyperLink cssclass="navbar-brand" id="lFuente"/>
                    <!-- Navbar Right Menu -->
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <li class="dropdown user user-menu">
                                <a href="index.php" class="dropdown-toggle" data-toggle="dropdown">
                                    <com:TImage id="tImgUser" ImageUrl="image/logos/pri.png" cssclass="user-image" Attributes.alt="User Image" />
                                    <span class="hidden-xs"><com:TLiteral id="tnombre" text ="Acceso Publico" /></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <!-- User image -->
                                    <li class="user-header">
                                        <com:TImage id="ImgUserPerfil" ImageUrl="image/logos/pri.png" cssclass="user-circle" Attributes.alt="PRI Hidalgo" />
                                            <p>
                                                <com:TLiteral id="lbPerfilNombre" />
                                                <small><com:TLiteral id="lbPerfilCargo" /> - <com:TLiteral id="lbPerfilAcceso" /></small>
                                            </p>
                                    </li>
                                    <!-- Menu Footer-->
                                    <li class="user-footer">
                                        <div class="pull-left">
                                          <a class="btn btn-default btn-flat" href="<%= $this->Service->constructUrl('sistema.perfil',[]) %>">
                                              <i class="fa fa-user"></i> Perfil
                                          </a>
                                        </div>
                                        <div class="pull-right">
                                          <com:TLinkButton CssClass="btn btn-default btn-flat"
                                                   OnClick="logoutButtonClicked"
                                                   Visible="<%= !$this->User->IsGuest %>"
                                                   CausesValidation="false" >
                                                   <i class="fa fa-sign-out"></i><span>Salir</span>
                                          </com:TLinkButton>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>
            <!-- Left side column. contains the logo and sidebar -->
            <aside class="main-sidebar">
                <!-- sidebar: style can be found in sidebar.less -->
                <section class="sidebar">
                    <!-- Sidebar user panel -->
                    <div class="user-panel">
                        <div class="pull-left image">
                            <com:TImage id="imgUser" ImageUrl="image/logos/pri.png" cssclass="user-image" Attributes.alt="User Image" />
                        </div>
                        <div class="pull-left info">
                            <p><com:TLiteral id="nombreUser" text="Defual" /></p>
                            <span class="user-options">
                                <!--- <a href="<%= $this->Service->constructUrl('login') %>"><i class="glyphicon glyphicon-log-in"></i></a> --->
                                <com:TLinkButton CssClass="login_out"
                                             OnClick="logoutButtonClicked"
                                             Visible="<%= !$this->User->IsGuest %>"
                                             CausesValidation="false" >
                                             <i class="glyphicon glyphicon-log-in"></i>
                            </com:TLinkButton>
                            </span>
                        </div>
                    </div>
                    <!-- sidebar menu: : style can be found in sidebar.less -->
                    <ul class="sidebar-menu">
                        <li class="header">Menú Principal</li>
                        <li>
                            <a href=".">
                                <i class="fa fa-home"></i>
                                <span> Principal</span>
                            </a>
                        </li>
                        <com:TRepeater ID="RpModulos" OnItemCreated="repeaterDataBound" >
                            <prop:ItemTemplate>
                        <li class="treeview">
                            <a href="#">
                                <i class="<%# $this->Data->icon %>"></i>
                                <span><%# $this->Data->nombre %></span>
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                                <com:TRepeater ID="RpServicios">
									<prop:HeaderTemplate>
                            <ul class="treeview-menu">
									</prop:HeaderTemplate>
                                    <prop:ItemTemplate>
                                <li>
                                    <a href="<%# $this->Service->constructUrl($this->Data->page_form,array('param' => $this->Data->param)) %>">
                                        <i class="<%# $this->Data->icon %>"></i> <%# $this->Data->nombre %>
                                    </a>
                                </li>
                                    </prop:ItemTemplate>
									<prop:FooterTemplate>
                            </ul>
									</prop:FooterTemplate>
                                </com:TRepeater>
                        </li>
                            </prop:ItemTemplate>
                        </com:TRepeater>
                        <li>
                            <com:TLinkButton CssClass="login_out"
                                             OnClick="logoutButtonClicked"
                                             Visible="<%= !$this->User->IsGuest %>"
                                             CausesValidation="false" >
                                             <i class="fa fa-sign-out"></i><span>Salir</span>
                            </com:TLinkButton>
                        </li>
                    </ul>
                </section>
                <!-- /.sidebar -->
            </aside>
            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>
                        <com:TLiteral id="titulo" text="Titulo de la pagina" />
                        <small><com:TLiteral id="subtitulo" text="subTitulo de la pagina" /></small>
                    </h1>
                    <ol class="breadcrumb">
                        <com:TLiteral id="barramenu" text='<li><a href="#"><i class="fa fa-home"></i> Home</a></li>' />
                    </ol>
                </section>
                <!-- Main content -->
                <section class="content">
                    <!-- Main row -->
                    <div class="row">
                        <com:TContentPlaceHolder ID="Main" />
                    </div>
                    <!-- /.row -->
                </section>
                <!-- /.content -->
            </div>
            <!-- /.content-wrapper -->
            <footer class="main-footer">
                <div id="ajax_evento" class="modal fade" role="dialog">
                  <div class="modal-dialog modal-gl">
                    <div class="modal-body">
                      <div class="box">
                        <div class="box-header with-border">
                          <div class="col-sm-12">
                            <img src="favicon/android-icon-36x36.png" />
                            <com:TLabel id="lAplication" />
                          </div>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body form-horizontal">
                          <div class="form-group">
                            <div class="col-sm-12" >
                              <img src="image/load.gif" />
                              <p id="ajax_mesanje">Por favor, espere mientras se procesan los datos, esto puede tardar algunos minutos</p>
                            </div>
                          </div>
                        </div>
                        <!-- /.box-body -->
                        <div class="box-footer clearfix" style="text-align: right;">
                          
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="pull-right hidden-xs">
                    <b>Version: <com:TLabel id="lVersion" /></b>
                </div>
                <strong>Copyright &copy; 2018 <com:THyperLink id="lnkAutor" />.</strong> All rights reserved. <com:THyperLink id="lProyecto" />
            </footer>
            <com:THiddenField ID="controlEquis" Value="ctl01" />
            <com:TCustomValidator ControlToValidate="controlEquis" ValidationGroup="DummyValidationGroup" />
        </div>
        <script src="vendor/almasaeed2010/adminlte/bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>
        <!-- FastClick -->
        <script src="vendor/almasaeed2010/adminlte/bower_components/fastclick/lib/fastclick.js"></script>
        <!-- AdminLTE App -->
        <script src="vendor/almasaeed2010/adminlte/dist/js/adminlte.min.js"></script>
        <com:TClientScript>
            $(document).ready(function () {
              
              $('.sidebar-menu').tree();
              
            })
          
            function Msg(mensaje,activo){
                $("#ajax_mesanje").html(mensaje);
                if(activo){
                    $("#ajax_evento").modal("show");
                }else{
                    $("#ajax_evento").modal("hide");
                }
            }
            
            function Enterkey(e){
              if(e.keyCode == 13){
                 $('#'+ide).click();
              }
            }
        </com:TClientScript>
    </com:TForm>
</body>
</html>