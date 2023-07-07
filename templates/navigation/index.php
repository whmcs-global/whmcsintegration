    <?php // print_r($_['user']);?>
	<ul>
        <li class="active">
            <a data-navigation="all" href="">All Groups</a>
        </li>
				<?php foreach ($_['groups'] as $group) { ?>
					<li><a href="#"> <?php p($group['group']); ?> </a>
						<ul style="padding-left: 44px;">
							<?php foreach($_['user'] as $user){ 
								if($user['gid'] == $group['group']){ ?>
									<li><?php  p($user['uid']); ?> </li>
								
							<?php } }?>
							</ul>
					</li>
				<?php } ?>
    </ul>	
