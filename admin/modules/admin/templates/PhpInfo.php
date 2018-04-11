      <div class="container-fluid">
				<div class="row-fluid">
					<div class="span12">
						<h3 class="page-title">
							<?php echo $title; ?>
						</h3>
						<!-- ul class="breadcrumb">
							<li>
								<i class="icon-home"></i>
								<a href="index.html">Home</a> 
								<i class="icon-angle-right"></i>
							</li>
							<li>
								<a href="#">Layouts</a>
								<i class="icon-angle-right"></i>
							</li>
							<li><a href="#">Blank Page</a></li>
						</ul -->
					</div>
				</div>
				<div class="row-fluid">
					<div class="span12">
            <style>
              table {
                table-layout: fixed;
                width: 100%;
              }
              
              td.e {
                width: 25% !important;
                font-weight: bold;
              }
              
              td.v {
                word-wrap: break-word;
              }
              
              th {
                text-align: left;
                background-color: #EFEFEF;
              }
              
              th:first-child {
                width: 25% !important;
              }
            </style>
						<?php
            ob_start();
            phpinfo();
            $pinfo = ob_get_contents();
            ob_end_clean();

            $pinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $pinfo);
            $pinfo = preg_replace('%width=".*?"%ms', '', $pinfo);
            $pinfo = preg_replace('%<th colspan="2">(.*?)</th>%ms', '<th>$1</th><th>&nbsp;</th>', $pinfo);
            echo $pinfo;
            ?>
					</div>
				</div>
			</div>