      <div class="container-fluid">
				<div class="row-fluid">
						<h3 class="page-title">
							<?php echo ucfirst($title); ?>
						</h3>
						<ul class="breadcrumb">
							<li>
								<i class="icon-home"></i>
                  <a href="./">Domů</a> 
								<i class="icon-angle-right"></i>
							</li>
							<li>
                <?php echo ucfirst($title); ?>
              </li>
						</ul>
					</div>
				</div>         
				<div class="row-fluid">
					<div class="span12 page-404">
						<div class="number">
							<?php echo trim(str_replace('-', '', filter_var($title, FILTER_SANITIZE_NUMBER_INT))); ?>
						</div>
						<div class="details">
              <h3><?php echo $text; ?></h3>
						</div>
					</div>
				</div>