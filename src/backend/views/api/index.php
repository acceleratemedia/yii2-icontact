<?php

use bvb\icontact\common\helpers\ApiHelper;

$this->title = 'iContact Api';

?>
<p>
    The following configuration is currently being used to connect to iContact:
    <ul>
    	<li>App ID: <?= Yii::$app->params['iContact']['appId']; ?></li>
    	<li>API Username: <?= Yii::$app->params['iContact']['apiUsername']; ?></li>
    	<li>Environment: <?= isset(Yii::$app->params['iContact']['useSandbox']) && Yii::$app->params['iContact']['useSandbox'] ? 'Sandbox' : 'Production' ?></li>
    </ul>
</p>

<hr />

<?php
$accounts = ApiHelper::getSingleton()->getInstance()->makeCall('/a/', 'get', null, 'accounts');
if(!empty($accounts)): ?>
	The following accounts were found:
	<ul>
	<?php foreach($accounts as $account):
?>
	<li>ID: <?= $account->accountId; ?>; Email: <?= $account->email; ?></li>
<?php endforeach;?>
	</ul>
<?php else: ?>
	The api returned no accounts
<?php endif; ?>
</p>

<p>The account ID returned from the API call: <?= ApiHelper::getSingleton()->getInstance()->setAccountId(); ?></p>
<p>The default client folder ID returned from the API call: <?= ApiHelper::getSingleton()->getInstance()->setClientFolderId(); ?></p>

<hr />

<p>
<?php
$clientFolders = ApiHelper::getSingleton()->getInstance()->makeCall('/a/'.ApiHelper::getSingleton()->getInstance()->setAccountId().'/c/', 'get', null, 'clientfolders');
if(!empty($clientFolders)): ?>
	The following client folders were found:
	<ul>
	<?php foreach($clientFolders as $clientFolder):
?>
		<li>
			ID: <?= $clientFolder->clientFolderId; ?>;
			<?php /**
			For some reason their sandbox API and production API return different
			sets of data so I need to check if the property exists here and display
			something
			**/ ?>
			<?php if(property_exists($clientFolder, 'emailRecipient')):  ?> Email Recipient: <?= $clientFolder->emailRecipient.';'; endif; ?>
			<?php if(property_exists($clientFolder, 'name')):  ?> Name: <?= $clientFolder->name; endif; ?>

			<?php
			ApiHelper::getSingleton()->getInstance()->setClientFolderId($clientFolder->clientFolderId);
			$lists = ApiHelper::getSingleton()->getInstance()->getLists();
			if(!empty($lists)): ?>
				<br />The following lists were found:
				<ul>
				<?php foreach($lists as $list):
			?>
				<li>
					<?= $list->name; ?> (ID: <?= $list->listId; ?>)<br />
					<?= $list->description; ?>
				</li>
			<?php endforeach;?>
				</ul>
			<?php else: ?>
				The api returned no lists
			<?php endif; ?>
		</li>
<?php endforeach;?>
	</ul>
<?php else: ?>
	The api returned no client folders
<?php endif; ?>
</p>


