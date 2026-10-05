{*+**********************************************************************************
* The contents of this file are subject to the vtiger CRM Public License Version 1.1
* ("License"); You may not use this file except in compliance with the License
* The Original Code is: vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
************************************************************************************}

{include file="modules/Vtiger/partials/Topbar.tpl"}

<div class="container-fluid app-nav">
	<div class="row">
		{include file="partials/SidebarHeader.tpl"|vtemplate_path:$MODULE}
		{include file="ModuleHeader.tpl"|vtemplate_path:$MODULE}
	</div>
</div>
</nav>
<div id='overlayPageContent' class='fade modal overlayPageContent content-area overlay-container-60' tabindex='-1' role='dialog' aria-hidden='true'>
	<div class="data">
	</div>
	<div class="modal-dialog">
	</div>
</div>  
<div class="main-container main-container-{$MODULE}">
		{assign var=LEFTPANELHIDE value=$CURRENT_USER_MODEL->get('leftpanelhide')}
		<div id="modnavigator" class="module-nav">
			<div class="hidden-xs hidden-sm mod-switcher-container">
				{include file="partials/Menubar.tpl"|vtemplate_path:$MODULE}
			</div>
		</div>
		<div id="sidebar-essentials" class="sidebar-essentials {if $LEFTPANELHIDE eq '1'} hide {/if}">
			{include file="partials/SidebarEssentials.tpl"|vtemplate_path:$MODULE}
		</div>
		{* KIROS mobile: chi 5 module co giao dien the (card) tren dien thoai (khop MODULES trong ts-mobile.js). Module khac giu bang goc cua vtiger. *}
		{assign var=TSM_ON value=0}
		{if $MODULE eq 'Leads' || $MODULE eq 'Potentials' || $MODULE eq 'Contacts' || $MODULE eq 'Accounts' || $MODULE eq 'Campaigns'}{assign var=TSM_ON value=1}{/if}
		{if $TSM_ON eq 1}
		{* thanh cong cu (Danh sach / Sap xep / Loc) render san tu server de trang mobile dung layout ngay tu dau (ts-mobile.js chi cap nhat trang thai). Desktop an bang CSS. *}
		{assign var=TSM_CVNAME value=''}
		{foreach key=TSM_GROUP_LABEL item=TSM_GROUP from=$CUSTOM_VIEWS}{foreach item=TSM_CV from=$TSM_GROUP}{if $VIEWID eq $TSM_CV->getId()}{assign var=TSM_CVNAME value={vtranslate($TSM_CV->get('viewname'), $MODULE)}}{/if}{/foreach}{/foreach}
		<div class="tsm-toolbar">
			<button type="button" class="tsm-chip" data-tsm-open="lists"><i class="fa fa-list-ul"></i><span class="tsm-chip-text">{$TSM_CVNAME|@escape:'html'}</span><i class="fa fa-angle-down"></i></button>
			<button type="button" class="tsm-tbtn tsm-sort-btn" data-tsm-open="sort" title="Sắp xếp" aria-label="Sắp xếp"><i class="fa fa-sort-amount-asc"></i></button>
			<button type="button" class="tsm-tbtn tsm-filter-btn" data-tsm-open="search"><i class="fa fa-filter"></i><span>Lọc</span><span class="tsm-badge" hidden></span></button>
		</div>
		{/if}
		<div class="listViewPageDiv content-area {if $LEFTPANELHIDE eq '1'} full-width {/if}{if $TSM_ON eq 1} tsm-cards-on{/if}" id="listViewContent">

