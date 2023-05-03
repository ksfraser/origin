<?php

//$path_to_faroot= dirname ( realpath ( __FILE__ ) ) . "/../..";
//$path_to_faroot = __DIR__ . "/../../";
//$path_to_ksfcommon = __DIR__ . "/";

require_once( 'origin.inc.php' );


//table stock_master
define( 'STOCK_ID_LENGTH_ORIG', 20 );
define( 'STOCK_ID_LENGTH', 64 );
define( 'DESCRIPTION_LENGTH', 200 );
define( 'ACCOUNTCODE_LENGTH', 15 );
define( 'GL_ACCOUNT_NAME_LENGTH', 32 );
//prod_variables
define( 'SLUG_LENGTH', 5 );

define( 'REFERENCE_LENGTH', 40 );
define( 'LOC_CODE_LENGTH', 5 );
//table stock_category
define( 'CAT_DESCRIPTION_LENGTH', 20 );

//table suppliers
define( 'SUPP_NAME_LENGTH', 60 );
define( 'SUPP_WEBSITE_LENGTH', 100 );
define( 'SUPP_REF_LENGTH', 30 );
define( 'SUPP_ACCOUNT_NO_LENGTH', 40 );

//set_global_stock_item(), get_global_stock_item()
//Need to check following functions
//write_customer_trans_detail_item()
//add_grn_to_trans() 
if( !defined( 'TB_PREF' ) )
	define( 'TB_PREF', "1_" );
$stock_id_tables = array();	//stock_id, item_code, stk_code, idx_stock_id, master_stock_id, child_stock_id, sku, barcode, slug, item_img_name
$stock_id_tables[] = array( 'table' => TB_PREF . 'bom', 'column' => 'parent', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );//Need to dbl check this one!
$stock_id_tables[] = array( 'table' => TB_PREF . 'bom', 'column' => 'component', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );//Need to dbl check this one!
$stock_id_tables[] = array( 'table' => TB_PREF . 'debtor_trans_details', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'grn_items', 'column' => 'item_code', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'item_codes', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'item_codes', 'column' => 'item_code', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'loc_stock', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'prices', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'purch_data', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH  );
$stock_id_tables[] = array( 'table' => TB_PREF . 'purch_order_details', 'column' => 'item_code', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'qoh', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'sales_order_details', 'column' => 'stk_code', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'stock_master', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'stock_moves', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'supp_invoice_items', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'wo_issue_items', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'wo_requirements', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'workorders', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
$stock_id_tables[] = array( 'table' => TB_PREF . 'woo', 'column' => 'stock_id', 'type' => 'VARCHAR', 'length' => STOCK_ID_LENGTH );
//$stock_id_tables[] = array( 'table' => TB_PREF . '', 'column' => 'stock_id' );
$stock_id_tables[] = array( 'table' => TB_PREF . 'purch_data', 'column' => 'supplier_description', 'type' => 'VARCHAR', 'length' => DESCRIPTION_LENGTH );



//$eventcount = 573000;
//define( 'KSF_FIELD_NOT_SET', $eventcount ); $eventcount++;	//Class Fields
?>

