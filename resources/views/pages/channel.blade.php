@section('customCss')
    <link href="{{ asset('css/sidebar.css') }}" rel="stylesheet" type="text/css">
@stop

@extends('layout')

@section('content')
	<!--collapsable sidebar taken from:
		https://bootstrapious.com/p/bootstrap-sidebar
	-->

    <div class="wrapper">
        <!-- Sidebar Holder -->
        <nav id="sidebar">
            <div class="sidebar-header">
                <h3>{{ $chan->platform === 0 ? 'Twitch Channel' : 'Youtube Channel' }}</h3>
            </div>

            <ul class="list-unstyled components">
                <p>Dummy Heading</p>
                <li class="active">
                    <a href="#homeSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Home</a>
                    <ul class="collapse list-unstyled" id="homeSubmenu">
                        <li>
                            <a href="#">Home 1</a>
                        </li>
                        <li>
                            <a href="#">Home 2</a>
                        </li>
                        <li>
                            <a href="#">Home 3</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#">About</a>
                    <a href="#pageSubmenu" data-toggle="collapse" aria-expanded="false" class="dropdown-toggle">Pages</a>
                    <ul class="collapse list-unstyled" id="pageSubmenu">
                        <li>
                            <a href="#">Page 1</a>
                        </li>
                        <li>
                            <a href="#">Page 2</a>
                        </li>
                        <li>
                            <a href="#">Page 3</a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#">Portfolio</a>
                </li>
                <li>
                    <a href="#">Contact</a>
                </li>
            </ul>

            <ul class="list-unstyled CTAs">
                <li>
                    <a href="https://bootstrapious.com/tutorial/files/sidebar.zip" class="download">Download source</a>
                </li>
                <li>
                    <a href="https://bootstrapious.com/p/bootstrap-sidebar" class="article">Back to article</a>
                </li>
            </ul>
        </nav>

        <!-- Page Content Holder -->
        <div id="content">
            <h1 class="text-center">
				<button type="button" id="sidebarCollapse" class="sidebar-btn">
				    <span></span>
				    <span></span>
				    <span></span>
				</button>
            	{{ $chan->channel_name }}
            </h1>
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>

            <div class="line"></div>

            <h2>Lorem Ipsum Dolor</h2>
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>

            <div class="line"></div>

            <h2>Lorem Ipsum Dolor</h2>
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>

            <div class="line"></div>

            <h3>Lorem Ipsum Dolor</h3>
            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
       

			<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
			    <h1 class="h2">Dashboard</h1>
			    <div class="btn-toolbar mb-2 mb-md-0">
			        <div class="btn-group mr-2">
			            <button class="btn btn-sm btn-outline-secondary">Share</button>
			            <button class="btn btn-sm btn-outline-secondary">Export</button>
			        </div>
			        <button class="btn btn-sm btn-outline-secondary dropdown-toggle">
			        <span data-feather="calendar"></span>
			        This week
			        </button>
			    </div>
			</div>
			<canvas class="my-4 w-100" id="myChart" width="900" height="380"></canvas>
			<h2>Section title</h2>
			<div class="table-responsive">
			    <table class="table table-striped table-sm">
			        <thead>
			            <tr>
			                <th>#</th>
			                <th>Header</th>
			                <th>Header</th>
			                <th>Header</th>
			                <th>Header</th>
			            </tr>
			        </thead>
			        <tbody>
			            <tr>
			                <td>1,001</td>
			                <td>Lorem</td>
			                <td>ipsum</td>
			                <td>dolor</td>
			                <td>sit</td>
			            </tr>
			            <tr>
			                <td>1,002</td>
			                <td>amet</td>
			                <td>consectetur</td>
			                <td>adipiscing</td>
			                <td>elit</td>
			            </tr>
			            <tr>
			                <td>1,003</td>
			                <td>Integer</td>
			                <td>nec</td>
			                <td>odio</td>
			                <td>Praesent</td>
			            </tr>
			            <tr>
			                <td>1,003</td>
			                <td>libero</td>
			                <td>Sed</td>
			                <td>cursus</td>
			                <td>ante</td>
			            </tr>
			            <tr>
			                <td>1,004</td>
			                <td>dapibus</td>
			                <td>diam</td>
			                <td>Sed</td>
			                <td>nisi</td>
			            </tr>
			            <tr>
			                <td>1,005</td>
			                <td>Nulla</td>
			                <td>quis</td>
			                <td>sem</td>
			                <td>at</td>
			            </tr>
			            <tr>
			                <td>1,006</td>
			                <td>nibh</td>
			                <td>elementum</td>
			                <td>imperdiet</td>
			                <td>Duis</td>
			            </tr>
			            <tr>
			                <td>1,007</td>
			                <td>sagittis</td>
			                <td>ipsum</td>
			                <td>Praesent</td>
			                <td>mauris</td>
			            </tr>
			            <tr>
			                <td>1,008</td>
			                <td>Fusce</td>
			                <td>nec</td>
			                <td>tellus</td>
			                <td>sed</td>
			            </tr>
			            <tr>
			                <td>1,009</td>
			                <td>augue</td>
			                <td>semper</td>
			                <td>porta</td>
			                <td>Mauris</td>
			            </tr>
			            <tr>
			                <td>1,010</td>
			                <td>massa</td>
			                <td>Vestibulum</td>
			                <td>lacinia</td>
			                <td>arcu</td>
			            </tr>
			            <tr>
			                <td>1,011</td>
			                <td>eget</td>
			                <td>nulla</td>
			                <td>Class</td>
			                <td>aptent</td>
			            </tr>
			            <tr>
			                <td>1,012</td>
			                <td>taciti</td>
			                <td>sociosqu</td>
			                <td>ad</td>
			                <td>litora</td>
			            </tr>
			            <tr>
			                <td>1,013</td>
			                <td>torquent</td>
			                <td>per</td>
			                <td>conubia</td>
			                <td>nostra</td>
			            </tr>
			            <tr>
			                <td>1,014</td>
			                <td>per</td>
			                <td>inceptos</td>
			                <td>himenaeos</td>
			                <td>Curabitur</td>
			            </tr>
			            <tr>
			                <td>1,015</td>
			                <td>sodales</td>
			                <td>ligula</td>
			                <td>in</td>
			                <td>libero</td>
			            </tr>
			        </tbody>
			    </table>
			</div>        
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function () {
            $('#sidebarCollapse').on('click', function () {
                $('#sidebar').toggleClass('active');
                $(this).toggleClass('active');
            });
        });
    </script>
@stop