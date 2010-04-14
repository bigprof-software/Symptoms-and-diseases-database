<?php

class DataList{
	// this class generates the data table ...

	var $QueryFieldsTV,
		$QueryFieldsCSV,
		$QueryFieldsFilters,
		$QueryFieldsQS,
		$QueryFrom,
		$QueryWhere,
		$QueryOrder,

		$ColWidth,                      // array of field widths
		$DataHeight,
		$TableName,

		$AllowSelection,
		$AllowDelete,
		$AllowDeleteOfParents,
		$AllowInsert,
		$AllowUpdate,
		$SeparateDV,
		$Permissions,
		$AllowFilters,
		$AllowSavingFilters,
		$AllowSorting,
		$AllowNavigation,
		$AllowPrinting,
		$AllowPrintingMultiSelection,
		$HideTableView,
		$AllowCSV,
		$CSVSeparator,

		$QuickSearch,     // 0 to 3

		$RecordsPerPage,
		$ScriptFileName,
		$RedirectAfterInsert,
		$TableTitle,
		$PrimaryKey,
		$DefaultSortField,
		$DefaultSortDirection,

		// Templates variables
		$Template,
		$SelectedTemplate,
		$ShowTableHeader, // 1 = show standard table headers
		$ShowRecordSlots, // 1 = show empty record slots in table view
		// End of templates variables

		$ContentType,    // set by DataList to 'tableview', 'detailview', 'tableview+detailview', 'print-tableview', 'print-detailview' or 'filters'
		$HTML;           // generated html after calling Render()

	function DataList(){     // Constructor function
		$this->DataHeight = 150;

		$this->AllowSelection = 1;
		$this->AllowDelete = 1;
		$this->AllowInsert = 1;
		$this->AllowUpdate = 1;
		$this->AllowFilters = 1;
		$this->AllowNavigation = 1;
		$this->AllowPrinting = 1;
		$this->HideTableView = 0;
		$this->QuickSearch = 0;
		$this->AllowCSV = 0;
		$this->CSVSeparator = ",";
		$this->HighlightColor = '#FFF0C2';  // default highlight color

		$this->RecordsPerPage = 10;
		$this->Template = '';
		$this->HTML = "";
	}

	function showTV(){
		if($this->SeparateDV){
			$this->HideTableView = ($this->Permissions[2]==0 ? 1 : 0);
		}
	}

	function hideTV(){
		if($this->SeparateDV){
			$this->HideTableView = 1;
		}
	}

	function Render(){
	// get post and get variables
		global $Translation;

		$FiltersPerGroup = 4;

		if($_SERVER['REQUEST_METHOD'] == 'GET'){
			$SortField = $_GET["SortField"];
			$SortDirection = $_GET["SortDirection"];
			$FirstRecord = $_GET["FirstRecord"];
			$ScrollUp_y = $_GET["ScrollUp_y"];
			$ScrollDn_y = $_GET["ScrollDn_y"];
			$Previous_x = $_GET["Previous_x"];
			$Next_x = $_GET["Next_x"];
			$Filter_x = $_GET["Filter_x"];
			$SaveFilter_x = $_GET["SaveFilter_x"];
			$NoFilter_x = $_GET["NoFilter_x"];
			$CancelFilter = $_GET["CancelFilter"];
			$ApplyFilter = $_GET["ApplyFilter"];
			$Search_x = $_GET["Search_x"];
			$SearchString = (get_magic_quotes_gpc() ? stripslashes($_GET['SearchString']) : $_GET['SearchString']);
			$CSV_x = $_GET["CSV_x"];

			$FilterAnd = $_GET["FilterAnd"];
			$FilterField = $_GET["FilterField"];
			$FilterOperator = $_GET["FilterOperator"];
			if(is_array($_GET['FilterValue'])){
				foreach($_GET['FilterValue'] as $fvi=>$fv){
					$FilterValue[$fvi]=(get_magic_quotes_gpc() ? stripslashes($fv) : $fv);
				}
			}

			$Print_x = $_GET["Print_x"];
			$SelectedID = (get_magic_quotes_gpc() ? stripslashes($_GET['SelectedID']) : $_GET['SelectedID']);
			$insert_x = $_GET["insert_x"];
			$update_x = $_GET["update_x"];
			$delete_x = $_GET["delete_x"];
			$SkipChecks = $_GET['confirmed'];
			$deselect_x = $_GET["deselect_x"];
			$addNew_x = $_GET["addNew_x"];
			$dvprint_x = $_GET['dvprint_x'];
		}else{
			$SortField = $_POST["SortField"];
			$SortDirection = $_POST["SortDirection"];
			$FirstRecord = $_POST["FirstRecord"];
			$ScrollUp_y = $_POST["ScrollUp_y"];
			$ScrollDn_y = $_POST["ScrollDn_y"];
			$Previous_x = $_POST["Previous_x"];
			$Next_x = $_POST["Next_x"];
			$Filter_x = $_POST["Filter_x"];
			$SaveFilter_x = $_POST["SaveFilter_x"];
			$NoFilter_x = $_POST["NoFilter_x"];
			$CancelFilter = $_POST["CancelFilter"];
			$ApplyFilter = $_POST["ApplyFilter"];
			$Search_x = $_POST["Search_x"];
			$SearchString = (get_magic_quotes_gpc() ? stripslashes($_POST['SearchString']) : $_POST['SearchString']);
			$CSV_x = $_POST["CSV_x"];

			$FilterAnd = $_POST['FilterAnd'];
			$FilterField = $_POST['FilterField'];
			$FilterOperator = $_POST['FilterOperator'];
			if(is_array($_POST['FilterValue'])){
				foreach($_POST['FilterValue'] as $fvi=>$fv){
					$FilterValue[$fvi]=(get_magic_quotes_gpc() ? stripslashes($fv) : $fv);
				}
			}

			$Print_x = $_POST['Print_x'];
			$PrintTV = $_POST['PrintTV'];
			$PrintDV = $_POST['PrintDV'];
			$SelectedID = (get_magic_quotes_gpc() ? stripslashes($_POST['SelectedID']) : $_POST['SelectedID']);
			$insert_x = $_POST['insert_x'];
			$update_x = $_POST['update_x'];
			$delete_x = $_POST['delete_x'];
			$SkipChecks = $_POST['confirmed'];
			$deselect_x = $_POST['deselect_x'];
			$addNew_x = $_POST['addNew_x'];
			$dvprint_x = $_POST['dvprint_x'];
		}

	// insure authenticity of user inputs:
		if(!$this->AllowDelete){
			$delete_x = "";
		}
		if(!$this->AllowDeleteOfParents){
			$SkipChecks = "";
		}
		if(!$this->AllowInsert){
			$insert_x = "";
			$addNew_x = "";
		}
		if(!$this->AllowUpdate){
			$update_x = "";
		}
		if(!$this->AllowFilters){
			$Filter_x = "";
		}
		if(!$this->AllowPrinting){
			$Print_x = '';
			$PrintDV = '';
			$PrintTV = '';
		}
		if(!$this->AllowPrintingMultiSelection){
			$PrintDV = '';
			$PrintTV = '';
		}
		if(!$this->QuickSearch){
			$SearchString = "";
		}
		if(!$this->AllowCSV){
			$CSV_x = "";
		}

	// enforce record selection if user has edit/delete permissions on the current table
		$AllowPrintDV=1;
		$this->Permissions=getTablePermissions($this->TableName);
		if($this->Permissions[3] || $this->Permissions[4]){ // current user can edit or delete?
			$this->AllowSelection = 1;
		}elseif(!$this->AllowSelection){
			$SelectedID='';
			$AllowPrintDV=0;
			$PrintDV='';
		}

		if(!$this->AllowSelection || !$SelectedID){ $dvprint_x=''; }

		$this->QueryFieldsIndexed=reIndex($this->QueryFieldsFilters);


		$this->HTML .= '<form method="post" name="myform" action="'.$this->ScriptFileName.'">';
		$this->HTML .= '<input type="submit" style="position: absolute; left: 0px; top: -100px;" onclick="return false;">';

		$this->ContentType='tableview'; // default content type

	// handle user commands ...
		if($PrintTV != ''){
			$Print_x=1;
			$_POST['Print_x']=1;
		}

		if($deselect_x != ''){
			$SelectedID = '';
			$this->showTV();
		}

		elseif($insert_x != ''){
			$SelectedID = call_user_func($this->TableName.'_insert');

			// redirect to a safe url to avoid refreshing and thus
			// insertion of duplicate records.

			// compose filters and sorting
			for($i = 1; $i <= (20 * $FiltersPerGroup); $i++){ // Number of filters allowed
				if($FilterField[$i] != "" && $FilterOperator[$i] != "" && ($FilterValue[$i] != "" || strstr($FilterOperator[$i], 'Empty'))){
					$filtersGET .= "&FilterAnd[$i]=$FilterAnd[$i]&FilterField[$i]=$FilterField[$i]&FilterOperator[$i]=$FilterOperator[$i]&FilterValue[$i]=".urlencode($FilterValue[$i]);
				}
			}
			$filtersGET .= "&SortField=$SortField&SortDirection=$SortDirection&FirstRecord=$FirstRecord";
			$filtersGET = substr($filtersGET, 1); // remove initial &

			if($this->RedirectAfterInsert!=""){
				if(strpos($this->RedirectAfterInsert, '?')){ $this->RedirectAfterInsert.='&record-added-ok='.rand(); }else{ $this->RedirectAfterInsert.='?record-added-ok='.rand(); }
				if(strpos($this->RedirectAfterInsert, $this->ScriptFileName)!==false){ $this->RedirectAfterInsert.='&'.$filtersGET; }
				$this->HTML .= "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0;url=" . str_replace("#ID#", urlencode($SelectedID), $this->RedirectAfterInsert) ."\">";
				return;
			}else{
				$this->HTML .= '<META HTTP-EQUIV="Refresh" CONTENT="0;url='.$this->ScriptFileName.'?SelectedID='.urlencode($SelectedID).'&'.$filtersGET.'">';
				return;
			}
		}

		elseif($delete_x != ""){
			$d = call_user_func($this->TableName.'_delete', $SelectedID, $this->AllowDeleteOfParents, $SkipChecks);
			if($d){
				$this->HTML .= "<div class=Error>".$Translation['error:']." $d</div>";
			}else{
				$SelectedID = "";
				$this->showTV();
			}
		}

		elseif($update_x != ""){
			call_user_func($this->TableName.'_update', $SelectedID);

			// compose filters and sorting
			for($i = 1; $i <= (20 * $FiltersPerGroup); $i++){ // Number of filters allowed
				if($FilterField[$i] != "" && $FilterOperator[$i] != "" && ($FilterValue[$i] != "" || strstr($FilterOperator[$i], 'Empty'))){
					$filtersGET .= "&FilterAnd[$i]=$FilterAnd[$i]&FilterField[$i]=$FilterField[$i]&FilterOperator[$i]=$FilterOperator[$i]&FilterValue[$i]=".urlencode($FilterValue[$i]);
				}
			}
			$filtersGET .= "&SortField=$SortField&SortDirection=$SortDirection&FirstRecord=$FirstRecord";
			$filtersGET = substr($filtersGET, 1); // remove initial &

			$this->HTML .= '<META HTTP-EQUIV="Refresh" CONTENT="0;url='.$this->ScriptFileName.'?SelectedID='.urlencode($SelectedID).'&'.$filtersGET.'&record-updated-ok='.rand().'">';
			return;
		}

		elseif($addNew_x != ""){
			$SelectedID='';
			$this->hideTV();
		}

		elseif($Print_x != ""){
			// print code here ....
			$this->AllowNavigation = 0;
			$this->AllowSelection = 0;
		}

		elseif($SaveFilter_x != "" && $this->AllowSavingFilters){
			$this->HTML .= "<table width=550 border=0 align=center><tr><td class=TableTitle>" . $Translation["saved filters title"] . "</td></tr>";
			$this->HTML .= "\n\t<tr><td class=TableHeader>" . $Translation["saved filters instructions"] . "</td></tr>";
			$this->HTML .= "\n\t<tr><td class=TableHeader><textarea cols=60 rows=12 wrap=off>";

			$SourceCode  = "<html><body>\n";
			$SourceCode .= '<form method="post" action="' . $_SERVER['HTTP_REFERER'] . '">'."\n";
			for($i = 1; $i <= (20 * $FiltersPerGroup); $i++){ // Number of filters allowed
				if($i%$FiltersPerGroup == 1 && $i != 1 && $FilterAnd[$i]!=""){
					$SourceCode .= "\t<input name=FilterAnd[$i] value='$FilterAnd[$i]' type=hidden>\n";
				}
				if($FilterField[$i] != "" && $FilterOperator[$i] != "" && ($FilterValue[$i] != "" || strstr($FilterOperator[$i], 'Empty'))){
					if(!strstr($SourceCode, "\t<input name=FilterAnd[$i] value=")){
						$SourceCode .= "\t<input name=FilterAnd[$i] value='$FilterAnd[$i]' type=hidden>\n";
					}
					$SourceCode .= "\t<input name=FilterField[$i] value='$FilterField[$i]' type=hidden>\n";
					$SourceCode .= "\t<input name=FilterOperator[$i] value='$FilterOperator[$i]' type=hidden>\n";
					$SourceCode .= "\t<input name=FilterValue[$i] value='" . htmlspecialchars($FilterValue[$i], ENT_QUOTES) . "' type=hidden>\n\n";
				}
			}
			$SourceCode .= "\n\t<input type=submit value=\"Show Filtered Data\">\n";
			$SourceCode .= "</form>\n</body></html>";
			$this->HTML .= $SourceCode;

			$this->HTML .= "</textarea>";
			$this->HTML .= "<br /><input type=submit value=\"" . $Translation["hide code"] . "\">";
			$this->HTML .= "\n\t</table>\n\n";
		}

		elseif($Filter_x != ""){
			if($this->FilterPage!=""){
				ob_start();
				@include($this->FilterPage);
				$out=ob_get_contents();
				ob_end_clean();
				$this->HTML .= $out;
			}else{
				// filter page code here .....
				$this->HTML .= '<table border="0" align="center"><tr><td colspan="4" class="TableTitle">' . $this->TableTitle . " " . $Translation['filters'] . "</td></tr>";
				$this->HTML .= "\n\t<tr><td class=\"TableHeader\"></td><td class=\"TableHeader\">" . $Translation['filtered field'] . '</td><td class="TableHeader">' . $Translation['comparison operator'] . '</td><td class="TableHeader">' . $Translation['comparison value'] . '</td></tr>';
				$this->HTML .= "\n\t<tr><td colspan=\"4\" class=\"TableHeader\"></td></tr>";

				for($i = 1; $i <= (3 * $FiltersPerGroup); $i++){ // Number of filters allowed
					$fields = "";
					$operators = "";

					if($i%$FiltersPerGroup == 1 && $i!=1){
						$this->HTML .= "\n\t<tr><td colspan=4 class=TableHeader></td></tr>";
						$this->HTML .= "\n\t<tr><td colspan=4 align=center>";
						$seland = new Combo;
						$seland->ListItem = array($Translation["or"], $Translation["and"]);
						$seland->ListData = array("or", "and");
						$seland->SelectName = "FilterAnd[$i]";
						$seland->SelectedData = $FilterAnd[$i];
						$seland->Render();
						$this->HTML .= $seland->HTML . "</td></tr>";
						$this->HTML .= "\n\t<tr><td colspan=4 class=TableHeader></td></tr>";
					}

					$this->HTML .= "\n\t<tr><td class=TableHeader style='text-align:left;'>&nbsp;" . $Translation["filter"] . sprintf("%02d", $i) . " ";

					// And, Or select
					if($i%$FiltersPerGroup != 1){
						$seland = new Combo;
						$seland->ListItem = array($Translation["and"], $Translation["or"]);
						$seland->ListData = array("and", "or");
						$seland->SelectName = "FilterAnd[$i]";
						$seland->SelectedData = $FilterAnd[$i];
						$seland->Render();
						$this->HTML .= $seland->HTML . "</td>";
					}else{
						$this->HTML .= "</td>";
					}

					// Fields list
					$selfields = new Combo;
					$selfields->SelectName = "FilterField[$i]";
					$selfields->SelectedData = $FilterField[$i];
					$selfields->ListItem = array_values($this->QueryFieldsFilters);
					$selfields->ListData = array_keys($this->QueryFieldsIndexed);
					$selfields->Render();
					$this->HTML .= "\n\t\t<td>$selfields->HTML</td>";


					// Operators list
					$selop = new Combo;
					$selop->ListItem = array($Translation["equal to"], $Translation["not equal to"], $Translation["greater than"], $Translation["greater than or equal to"], $Translation["less than"], $Translation["less than or equal to"] , $Translation["like"] , $Translation["not like"], $Translation["is empty"], $Translation["is not empty"]);
					$selop->ListData = array("<=>", "!=", ">", ">=", "<", "<=", "like", "not like", "isEmpty", "isNotEmpty");
					$selop->SelectName = "FilterOperator[$i]";
					$selop->SelectedData = $FilterOperator[$i];
					$selop->Render();
					$this->HTML .= "\n\t\t<td>$selop->HTML</td>";


					// Comparison expression
					$this->HTML .= "\n\t\t<td><input size=25 type=text name=FilterValue[$i] value=\"" . htmlspecialchars($FilterValue[$i], ENT_QUOTES) . "\" class=TextBox></td></tr>";

					if(!$i % $FiltersPerGroup){
						$this->HTML .= "\n\t<tr><td colspan=4 class=TableHeader></td></tr>";
					}
				}
				$this->HTML .= "\n\t<tr><td colspan=4 class=TableHeader></td></tr>";
				$this->HTML .= "\n\t<tr><td colspan=4 align=right><input type=image src=applyFilters.gif alt='" . $Translation["apply filters"] . "'>" . ($this->AllowSavingFilters ? " &nbsp; <input type=image src=save_search.gif alt='" . $Translation["save filters"] . "' name=SaveFilter>" : "") . "</td></tr>";
				$this->HTML .= "\n</table>";

			}
			// hidden variables ....
				$this->HTML .= "<input name=SortField value='".(is_numeric($SortField)? $SortField : $SortFieldNumeric)."' type=hidden>";               
				$this->HTML .= "<input name=SortDirection type=hidden value='$SortDirection'>";               
				$this->HTML .= "<input name=FirstRecord type=hidden value='1'>";              

				$this->ContentType='filters';
			return;
		}

		elseif($NoFilter_x != ""){
			// clear all filters ...
			for($i = 1; $i <= (20 * $FiltersPerGroup); $i++){ // Number of filters allowed
				$FilterField[$i] = "";
				$FilterOperator[$i] = "";
				$FilterValue[$i] = "";
			}
			$SearchString = "";
		}

		elseif($SelectedID){
			$this->hideTV();
		}

		if($SearchString != ''){
			if($Search_x!=''){ $FirstRecord=1; }

			if($this->QueryWhere=='')
				$this->QueryWhere = "where ";
			else
				$this->QueryWhere .= " and ";

			foreach($this->QueryFieldsQS as $fName => $fCaption){
				if(strpos($fName, '<img')===False){
					$this->QuerySearchableFields[$fName]=$fCaption;
				}
			}

			$this->QueryWhere.='('.implode(" LIKE '%".makeSafe($SearchString)."%' or ", array_keys($this->QuerySearchableFields))." LIKE '%".makeSafe($SearchString)."%')";
		}


	// set query filters
		$QueryHasWhere = 0;
		if(strpos($this->QueryWhere, 'where ')!==FALSE)
			$QueryHasWhere = 1;

		$WhereNeedsClosing = 0;
		for($i = 1; $i <= (20 * $FiltersPerGroup); $i+=$FiltersPerGroup){ // Number of filters allowed
			// test current filter group
			$GroupHasFilters = 0;
			for($j = 0; $j < $FiltersPerGroup; $j++){
				if($FilterField[$i+$j] != "" && $FilterOperator[$i+$j] != "" && ($FilterValue[$i+$j] != "" || strstr($FilterOperator[$i+$j], 'Empty'))){
					$GroupHasFilters = 1;
					break;
				}
			}

			if($GroupHasFilters){
				if(!stristr($this->QueryWhere, "where "))
					$this->QueryWhere = "where (";
				elseif($QueryHasWhere){
					$this->QueryWhere .= " and (";
					$QueryHasWhere = 0;
				}

				$this->QueryWhere .= " <FilterGroup> " . $FilterAnd[$i] . " (";

				for($j = 0; $j < $FiltersPerGroup; $j++)
					if($FilterField[$i+$j] != "" && $FilterOperator[$i+$j] != "" && ($FilterValue[$i+$j] != "" || strstr($FilterOperator[$i+$j], 'Empty'))){
						if($FilterAnd[$i+$j]==''){
							$FilterAnd[$i+$j]='and';
						}
						// test for date/time fields
						$tries=0; $isDateTime=FALSE; $isDate=FALSE;
						$fieldName=str_replace('`', '', $this->QueryFieldsIndexed[($FilterField[$i+$j])]);
						list($tn, $fn)=explode('.', $fieldName);
						while(!($res=sql("show columns from `$tn` like '$fn'")) && $tries<2){
							$tn=substr($tn, 0, -1);
							$tries++;
						}
						if($row=@mysql_fetch_array($res)){
							if($row['Type']=='date' || $row['Type']=='time'){
								$isDateTime=TRUE;
								if($row['Type']=='date'){
									$isDate=True;
								}
							}
						}
						// end of test
						if($FilterOperator[$i+$j]=='isEmpty' && !$isDateTime){
							$this->QueryWhere .= " <FilterItem> " . $FilterAnd[$i+$j] . " (" . $this->QueryFieldsIndexed[($FilterField[$i+$j])] . "='' or " . $this->QueryFieldsIndexed[($FilterField[$i+$j])] . " is NULL) </FilterItem>";
						}elseif($FilterOperator[$i+$j]=='isNotEmpty' && !$isDateTime){
							$this->QueryWhere .= " <FilterItem> " . $FilterAnd[$i+$j] . " " . $this->QueryFieldsIndexed[($FilterField[$i+$j])] . "!='' </FilterItem>";
						}elseif($FilterOperator[$i+$j]=='isEmpty' && $isDateTime){
							$this->QueryWhere .= " <FilterItem> " . $FilterAnd[$i+$j] . " (" . $this->QueryFieldsIndexed[($FilterField[$i+$j])] . "=0 or " . $this->QueryFieldsIndexed[($FilterField[$i+$j])] . " is NULL) </FilterItem>";
						}elseif($FilterOperator[$i+$j]=='isNotEmpty' && $isDateTime){
							$this->QueryWhere .= " <FilterItem> " . $FilterAnd[$i+$j] . " " . $this->QueryFieldsIndexed[($FilterField[$i+$j])] . "!=0 </FilterItem>";
						}elseif($FilterOperator[$i+$j]=='like' && !strstr($FilterValue[$i+$j], "%") && !strstr($FilterValue[$i+$j], "_")){
							$this->QueryWhere .= " <FilterItem> " . $FilterAnd[$i+$j] . " " . $this->QueryFieldsIndexed[($FilterField[$i+$j])] . " " . $FilterOperator[$i+$j] . " '%" . makeSafe($FilterValue[$i+$j]) . "%' </FilterItem>";
						}elseif($FilterOperator[$i+$j]=='not like' && !strstr($FilterValue[$i+$j], "%") && !strstr($FilterValue[$i+$j], "_")){
							$this->QueryWhere .= " <FilterItem> " . $FilterAnd[$i+$j] . " " . $this->QueryFieldsIndexed[($FilterField[$i+$j])] . " " . $FilterOperator[$i+$j] . " '%" . makeSafe($FilterValue[$i+$j]) . "%' </FilterItem>";
						}elseif($isDate){
							$dateValue = toMySQLDate($FilterValue[$i+$j]);
							$this->QueryWhere .= " <FilterItem> " . $FilterAnd[$i+$j] . " " . $this->QueryFieldsIndexed[($FilterField[$i+$j])] . " " . $FilterOperator[$i+$j] . " '$dateValue' </FilterItem>";
						}else{
							$this->QueryWhere .= " <FilterItem> " . $FilterAnd[$i+$j] . " " . $this->QueryFieldsIndexed[($FilterField[$i+$j])] . " " . $FilterOperator[$i+$j] . " '" . makeSafe($FilterValue[$i+$j]) . "' </FilterItem>";
						}
				}

				$this->QueryWhere .= ") </FilterGroup>";
				$WhereNeedsClosing = 1;
			}
		}

		if($WhereNeedsClosing)
			$this->QueryWhere .= ")";
	// set query sort
		if(!stristr($this->QueryOrder, "order by ") && $SortField != "" && $this->AllowSorting)
			$this->QueryOrder = "order by $SortField $SortDirection";

	// clean up query
		$this->QueryWhere = str_replace("( <FilterGroup> and ", "( ", $this->QueryWhere);
		$this->QueryWhere = str_replace("( <FilterGroup> or ", "( ", $this->QueryWhere);
		$this->QueryWhere = str_replace("( <FilterItem> and ", "( ", $this->QueryWhere);
		$this->QueryWhere = str_replace("( <FilterItem> or ", "( ", $this->QueryWhere);
		$this->QueryWhere = str_replace("<FilterGroup>", "", $this->QueryWhere);
		$this->QueryWhere = str_replace("</FilterGroup>", "", $this->QueryWhere);
		$this->QueryWhere = str_replace("<FilterItem>", "", $this->QueryWhere);
		$this->QueryWhere = str_replace("</FilterItem>", "", $this->QueryWhere);

	// if no 'order by' clause found, apply default sorting if specified
		if($this->DefaultSortField!="" && $this->QueryOrder==''){
			$this->QueryOrder="order by ".$this->DefaultSortField." ".$this->DefaultSortDirection;
		}

	// get count of matching records ...
		$TempQuery = 'SELECT count(1) from '.$this->QueryFrom.' '.$this->QueryWhere;
		$RecordCount = sqlValue($TempQuery);
		$FieldCountTV = count($this->QueryFieldsTV);
		$FieldCountCSV = count($this->QueryFieldsCSV);
		$FieldCountFilters = count($this->QueryFieldsFilters);
		if(!$RecordCount){
			$FirstRecord=1;
		}

	// disable multi-selection if too many records to avoid browser performance issues
		if($RecordCount > 1000) $this->AllowPrintingMultiSelection=0;

	// Output CSV on request
		if($CSV_x != ''){
			$this->HTML = '';

		// execute query for CSV output
			$fieldList='';
			foreach($this->QueryFieldsCSV as $fn=>$fc)
				$fieldList.="$fn as `$fc`, ";
			$fieldList=substr($fieldList, 0, -2);
			$csvQuery = 'SELECT '.$fieldList.' from '.$this->QueryFrom.' '.$this->QueryWhere.' '.$this->QueryOrder;

			// hook: table_csv
			if(function_exists($this->TableName.'_csv')){
				$args=array();
				$mq=call_user_func($this->TableName.'_csv', $csvQuery, getMemberInfo(), $args);
				$csvQuery=($mq ? $mq : $csvQuery);
			}

			$result = sql($csvQuery);

		// output CSV field names
			for($i = 0; $i < $FieldCountCSV; $i++)
				$this->HTML .= "\"" . mysql_field_name($result, $i) . "\"" . $this->CSVSeparator;
			$this->HTML .= "\n\n";

		// output CSV data
			while($row = mysql_fetch_row($result)){
				for($i = 0; $i < $FieldCountCSV; $i++)
					$this->HTML .= "\"" . str_replace(array("\r\n", "\r", "\n", '"'), array(' ', ' ', ' ', '""'), $row[$i]) . "\"" . $this->CSVSeparator;
				$this->HTML .= "\n\n";
			}
			$this->HTML = str_replace($this->CSVSeparator . "\n\n", "\n", $this->HTML);
			$this->HTML = substr($this->HTML, 0, strlen($this->HTML) - 1);

		// clean any output buffers
			while(@ob_end_clean());

		// output CSV HTTP headers ...
			header('HTTP/1.1 200 OK');
			header('Date: ' . @date("D M j G:i:s T Y"));
			header('Last-Modified: ' . @date("D M j G:i:s T Y"));
			header("Content-Type: application/force-download");
			header("Content-Lenght: " . (string)(strlen($this->HTML)));
			header("Content-Transfer-Encoding: Binary");
			header("Content-Disposition: attachment; filename=$this->TableName.csv");

		// send output and quit script
			echo $this->HTML;
			exit;
		}
		$t = time(); // just a random number for any purpose ...

		//$this->HTML .= "<font face=garamond>".$this->Query."</font>";  // uncomment this line for debugging

	// should SelectedID be reset on clicking TV buttons?
		$resetSelection=($this->SeparateDV ? "document.myform.SelectedID.value=''; " : '');

	// begin table and display table title
		$this->HTML .= "<table align=center cellspacing=1 cellpadding=0 border=0><tr>\n";
		$this->HTML .= "<td colspan=" . ($FieldCountTV + 2) . ">";
		$sum_width = 0;
		for($i = 0; $i < count($this->ColWidth); $i++)
			$sum_width += $this->ColWidth[$i];
		$this->HTML .= "<table" . ($this->HideTableView ? "" : " width=100%") . " cellspacing=0 cellpadding=0 border=0>".(($dvprint_x && $this->AllowSelection && $SelectedID) ? '' : "<tr><td align=left colspan=2><div class=TableTitle>$this->TableTitle</div><br /></td></tr>");

		if(!$this->HideTableView && !($dvprint_x && $this->AllowSelection && $SelectedID) && !$PrintDV){
			$this->HTML .= "<tr>";
			// display tables navigator menu
			if($Print_x==''){
				$this->HTML .= "<td align=left>" . NavMenus() . "</td>";
			}else{
				$this->HTML .= "\n<style type=\"text/css\">@media print{.displayOnly {display: none;}}</style>\n";
				if($this->AllowPrintingMultiSelection){
					$withSelected=''.
						'<input class="print-button" type="button" id="selectAll" value="'.$Translation['Select all records'].'" onClick="$(\'toggleAll\').checked=!$(\'toggleAll\').checked; toggleAllRecords();">'.
						'<span id="withSelected">'.
							'<input class="print-button" type="submit" name="PrintTV" value="'.$Translation['Print Preview Table View'].'">'.
							($AllowPrintDV ? '<input id="PrintDV" class="print-button" type="submit" name="PrintDV" value="'.$Translation['Print Preview Detail View'].'">' : '').
							'<input class="print-button" type="submit" name="Print_x" value="'.$Translation['Cancel Selection'].'">'.
						' &nbsp;</span>'.
						'<script>'.
							'var countSelected=0; '.
							'document.observe(\'dom:loaded\', function(){ '.
								'setInterval("'.
									'$(\'withSelected\').style.display=(countSelected ? \'inline\' : \'none\');'.
								'", 500); '.
							'});'.
						'</script>';
				}

				$this->HTML .= "\n".'<td colspan="2" class="displayOnly" style="min-width: 65em;"><div>'.
										'<input class="print-button" type="submit" value="'.$Translation['Cancel Printing'].'">'.
										'<input class="print-button" type="button" id="sendToPrinter" value="'.$Translation['Print'].'" onClick="window.print();">'.
										$withSelected.
									'</div></td>'."\n";
			}

			// display quick search box
			if($this->QuickSearch > 0 && $this->QuickSearch < 4 && $Print_x==''){
				if($this->QuickSearch==1 || $this->QuickSearch==2){
					$this->HTML .= '</tr><tr>';
				}
				$this->HTML .= "<td><div class=TableBodySelected style='text-align:" . ( ($this->QuickSearch == 1) ? "left" : (($this->QuickSearch == 2) ? "center" : "right")) . ";'>";
				$this->HTML .= "<nobr><b>" . $this->QuickSearchText . "</b> <input type=text name=SearchString value='" . htmlspecialchars($SearchString, ENT_QUOTES) . "' size=15 class=TextBox>";
				$this->HTML .= "<input onClick=\"$resetSelection document.myform.NoDV.value=1;\" align=top border=0 name=Search type=image vspace=2 hspace=2 src=qsearch.gif alt='" . $this->QuickSearchText . "'>";
				$this->HTML .= "</nobr></div></td>";
			}
			$this->HTML .= "</tr>";
			$this->HTML .= "<tr><td colspan=2><div class=\"TableBody\" style=\"text-align:center;\"><nobr>";

			// display 'Add New' icon
			if($this->Permissions[1] && $this->SeparateDV && $Print_x=='')
				$this->HTML .= " <input type=image src=addNew.gif name=addNew alt='" . $Translation['add new record'] . "'>";

			// display Print icon
			if($this->AllowPrinting && $Print_x=='')
				$this->HTML .= " <input onClick=\"document.myform.NoDV.value=1;\" type=image src=print.gif name=Print alt='" . $Translation["printer friendly view"] . "'>";

			// display CSV icon
			if($this->AllowCSV && $Print_x=='')
				$this->HTML .= " <input onClick=\"document.myform.NoDV.value=1;\" type=image src=csv.gif name=CSV alt='" . $Translation["save as csv"] . "'>";

			// display Filter icons
			if($this->AllowFilters && $Print_x=='')
				$this->HTML .= " <input onClick=\"document.myform.NoDV.value=1;\" type=image src=search.gif name=Filter alt='" . $Translation["edit filters"] . "'> <input onClick=\"$resetSelection document.myform.NoDV.value=1;\" type=image src=cancel_search.gif name=NoFilter alt='" . $Translation["clear filters"] . "'> ";
			$this->HTML .= "</nobr></div></td></tr>";

			$this->HTML .= "<!--</td></tr>--></table></td></tr>";
			$this->HTML .= "<tr><td class=TableHeader>".($this->AllowSelection ? "&nbsp;&nbsp;" : "")."</td>";
			if($this->AllowPrintingMultiSelection && $Print_x!='') $this->HTML .= '<td class="TableHeader displayOnly" align="left"><input type="checkbox" title="'.$Translation['Select all records'].'" id="toggleAll" onclick="toggleAllRecords();"></td>';
		// Templates
			if($this->Template!=''){
				$rowTemplate = @implode('', @file('./'.$this->Template));
				if(!$rowTemplate){
					$rowTemplate='';
					$selrowTemplate = '';
				}else{
					if($this->SelectedTemplate!=''){
						$selrowTemplate = @implode('', @file('./'.$this->SelectedTemplate));
						if(!$selrowTemplate){
							$selrowTemplate='';
						}
					}else{
						$selrowTemplate = '';
					}
				}
			}else{
				$rowTemplate = '';
				$selrowTemplate = '';
			}

			// process translations
			if($rowTemplate){
				foreach($Translation as $symbol=>$trans){
					$rowTemplate=str_replace("<%%TRANSLATION($symbol)%%>", $trans, $rowTemplate);
				}
			}
			if($selrowTemplate){
				foreach($Translation as $symbol=>$trans){
					$selrowTemplate=str_replace("<%%TRANSLATION($symbol)%%>", $trans, $selrowTemplate);
				}
			}
		// End of templates

		// display table headers
			global $SortFieldNumeric;
			if($rowTemplate=='' || $this->ShowTableHeader==1){
				for($i = 0; $i < count($this->ColCaption); $i++){
					if($this->AllowSorting == 1){
						$sort1 = "<a href=\"{$this->ScriptFileName}?SortDirection=asc&SortField=".($this->ColNumber[$i])."\" onClick=\"$resetSelection document.myform.NoDV.value=1; document.myform.SortDirection.value='asc'; document.myform.SortField.value = '".($this->ColNumber[$i])."'; document.myform.submit(); return false;\" class=\"TableHeader\">";
						$sort2 = "</a>";
						if(($this->ColNumber[$i] == $SortField)||($this->ColNumber[$i] == $SortFieldNumeric)){
							$SortDirection = ($SortDirection == "asc" ? "desc" : "asc");
							$sort1 = "<a href=\"{$this->ScriptFileName}?SortDirection=$SortDirection&SortField=".($this->ColNumber[$i])."\" onClick=\"$resetSelection document.myform.NoDV.value=1; document.myform.SortDirection.value='$SortDirection'; document.myform.SortField.value = ".($this->ColNumber[$i])."; document.myform.submit(); return false;\" class=\"TableHeader\"><img src=\"$SortDirection.gif\" border=\"0\" hspace=\"3\">";
							$SortDirection = ($SortDirection == "asc" ? "desc" : "asc");
						}
					}else{
						$sort1 = '';
						$sort2 = '';
					}
					$this->HTML .= "\t<td valign=top nowrap width='" . ($this->ColWidth[$i] ? $this->ColWidth[$i] : 100) . "' class=TableHeader><div class=TableHeader>$sort1" . $this->ColCaption[$i] . "$sort2</div></td>\n";
				}
			}else{
				// Display a Sort by drop down
				$this->HTML .= "\t<td valign=top class=TableHeader colspan=".($FieldCountTV+1)."><div class=TableHeader>";

				if($this->AllowSorting == 1){
					$sortCombo = new Combo;
					//$sortCombo->ListItem[] = "";
					//$sortCombo->ListData[] = "";
					for($i=0; $i < count($this->ColCaption); $i++){
						$sortCombo->ListItem[] = $this->ColCaption[$i];
						$sortCombo->ListData[] = $this->ColNumber[$i];
					}
					$sortCombo->SelectName = "FieldsList";
					$sortCombo->SelectedData = is_numeric($SortField) ? $SortField : $SortFieldNumeric;
					$sortCombo->Class = 'TableBody';
					$sortCombo->SelectedClass = 'TableBodySelected';
					$sortCombo->Render();
					$d = $sortCombo->HTML;
					$d = str_replace('<select ', "<select onChange=\"document.myform.SortDirection.value='$SortDirection'; document.myform.SortField.value=document.myform.FieldsList.value; document.myform.NoDV.value=1; document.myform.submit();\" ", $d);
					if($SortField){
						$SortDirection = ($SortDirection == "desc" ? "asc" : "desc");
						$sort = "<a href=\"javascript: document.myform.NoDV.value=1; document.myform.SortDirection.value='$SortDirection'; document.myform.SortField.value='$SortField'; document.myform.submit();\" class=TableHeader><img src=$SortDirection.gif border=0 width=11 height=11 hspace=3></a>";
						$SortDirection = ($SortDirection == "desc" ? "asc" : "desc");                  
					}else{
						$sort='';
					}

					$this->HTML .= $Translation['order by']." $d $sort";
				}
				$this->HTML .= "</div></td>\n";
			}

		// table view navigation code ...
			if($RecordCount && $this->AllowNavigation && $RecordCount>$this->RecordsPerPage){
				while($FirstRecord > $RecordCount)
					$FirstRecord -= $this->RecordsPerPage;

				if($FirstRecord == "" || $FirstRecord < 1)
					$FirstRecord = 1;

				if($Previous_x != ""){
					$FirstRecord -= $this->RecordsPerPage;
					if($FirstRecord <= 0)
						$FirstRecord = 1;
				}elseif($Next_x != ""){
					$FirstRecord += $this->RecordsPerPage;
					if($FirstRecord > $RecordCount)
						$FirstRecord = $RecordCount - ($RecordCount % $this->RecordsPerPage) + 1;
					if($FirstRecord > $RecordCount)
						$FirstRecord = $RecordCount - $this->RecordsPerPage + 1;
					if($FirstRecord <= 0)
						$FirstRecord = 1;
				}else{
					// no scrolling action took place :)
				}

			}elseif($RecordCount){
				$FirstRecord = 1;
				$this->RecordsPerPage = $RecordCount;
			}
		// end of table view navigation code

			$this->HTML .= "\n\t</tr>\n";
			$this->HTML = "<script>
					function colorize(item, color){
						var n=item.childNodes.length;
						for(var i=0; i<n; i++){
							if(item.childNodes[i].nodeName=='TD'){
								item.childNodes[i].style.backgroundColor=color;
								if(item.childNodes[i].childNodes.length>0){
									if(item.childNodes[i].childNodes[0].nodeName=='A'){
										item.childNodes[i].childNodes[0].style.backgroundColor=color;
									}
								}
							}
						}
					}
				</script>" . $this->HTML . '<!-- tv data below -->';

			$i = 0;
			$hc=new HtmlFilter();
			$hc->encoding='iso-8859-1';
			$hc->defaultProtocol='';
			if($RecordCount){
				$i = $FirstRecord;
			// execute query for table view
				$fieldList='';
				foreach($this->QueryFieldsTV as $fn=>$fc)
					$fieldList.="$fn as `$fc`, ";
				$fieldList=substr($fieldList, 0, -2);
				if($this->PrimaryKey)
					$fieldList.=", $this->PrimaryKey as '".str_replace('`', '', $this->PrimaryKey)."'";
				$tvQuery = 'SELECT '.$fieldList.' from '.$this->QueryFrom.' '.$this->QueryWhere.' '.$this->QueryOrder;
				$result = sql($tvQuery . " limit " . ($i-1) . ",$this->RecordsPerPage");
				while(($row = mysql_fetch_array($result)) && ($i < ($FirstRecord + $this->RecordsPerPage))){
					$alt=(($i-$FirstRecord)%2);
					if($PrintTV && $_POST["select_{$row[$FieldCountTV]}"]!=1)    continue;					$class = "TableBody".($alt ? "Selected" : "").($fNumeric ? "Numeric" : "");
					$this->HTML .= "\t<tr onMouseOver=\"colorize(this, '".$this->HighlightColor."');\" onMouseOut=\"colorize(this, '');\">";
					$this->HTML .= "<td class=$class valign=top align=right width=12>".($SelectedID == $row[$FieldCountTV] ? "<font color=red>&rArr;</font>" : "&nbsp;")."</td>";
					if($this->AllowPrintingMultiSelection && $Print_x!=''){
						$this->HTML .= "<td class=\"$class displayOnly\" valign=\"top\" align=\"left\" width=\"12\"><input type=\"checkbox\" id=\"select_{$row[$FieldCountTV]}\" name=\"select_{$row[$FieldCountTV]}\" value=\"1\" onclick=\"if(\$('select_{$row[$FieldCountTV]}').checked) countSelected++; else countSelected--;\"></td>";
						$toggleAllScript.="\$('select_{$row[$FieldCountTV]}').checked=s;";
					}
					// templates
					if($rowTemplate!=''){
						if($this->AllowSelection == 1 && $SelectedID == $row[$FieldCountTV] && $selrowTemplate!=""){
							$rowTemp=$selrowTemplate;
						}else{
							$rowTemp = $rowTemplate;
						}

						if($this->AllowSelection == 1 && $SelectedID != $row[$FieldCountTV]){
							$rowTemp = str_replace('<%%SELECT%%>',"<a onclick=\"document.myform.SelectedField.value=this.parentNode.cellIndex; document.myform.SelectedID.value='" . addslashes($row[$FieldCountTV]) . "'; document.myform.submit(); return false;\" href=\"{$this->ScriptFileName}?SelectedID=" . htmlspecialchars($row[$FieldCountTV], ENT_QUOTES) . "\" class=\"$class\" style=\"display: block; padding:0px;\">",$rowTemp);
							$rowTemp = str_replace('<%%ENDSELECT%%>','</a>',$rowTemp);
						}else{
							$rowTemp = str_replace('<%%SELECT%%>',"",$rowTemp);
							$rowTemp = str_replace('<%%ENDSELECT%%>','',$rowTemp);
						}

						for($j = 0; $j < $FieldCountTV; $j++){
							$fieldTVCaption=current(array_slice($this->QueryFieldsTV, $j, 1));

							$fd=$hc->clean($row[$j]);
							/*
								the TV template could contain field placeholders in the format 
								<%%FIELD_n%%> or <%%VALUE(Field Caption)%%> 
							*/
							$rowTemp = str_replace("<%%FIELD_$j%%>", thisOr($fd), $rowTemp);
							$rowTemp = str_replace("<%%VALUE($fieldTVCaption)%%>", thisOr($fd), $rowTemp);
							if(thisOr($fd)=='&nbsp;' && preg_match('/<a href=".*?&nbsp;.*?<\/a>/i', $rowTemp, $m)){
								$rowTemp=str_replace($m[0], '', $rowTemp);
							}
						}

						if($alt && $SelectedID != $row[$FieldCountTV]){
							$rowTemp = str_replace("TableBody", "TableBodySelected", $rowTemp);
							$rowTemp = str_replace("TableBodyNumeric", "TableBodySelectedNumeric", $rowTemp);
							$rowTemp = str_replace("SelectedSelected", "Selected", $rowTemp);
						}

						if($SearchString!='') $rowTemp=highlight($SearchString, $rowTemp);
						$this->HTML .= $rowTemp;
						$rowTemp = '';

					}else{
					// end of templates
						for($j = 0; $j < $FieldCountTV; $j++){
							$fType=mysql_field_type($result, $j);
							$fNumeric=(stristr($fType,'int') || stristr($fType,'float') || stristr($fType,'decimal') || stristr($fType,'numeric') || stristr($fType,'real') || stristr($fType,'double')) ? true : false;
							if($this->AllowSelection == 1){
								$sel1 = "<a href=\"{$this->ScriptFileName}?SelectedID=" . htmlspecialchars($row[$FieldCountTV], ENT_QUOTES) . "\" onclick=\"document.myform.SelectedID.value='" . addslashes($row[$FieldCountTV]) . "'; document.myform.submit(); return false;\" class=\"$class\" style=\"padding:0px;\">";
								$sel2 = "</a>";
							}else{
								$sel1 = "";
								$sel2 = "";
							}

							$this->HTML .= "<td valign=top class=$class><div class=$class>&nbsp;$sel1" . $row[$j] . "$sel2&nbsp;</div></td>";
						}
					}
					$this->HTML .= "</tr>\n";
					$i++;
				}
				$i--;
			}

			$this->HTML = preg_replace("/<a href=\"(mailto:)?&nbsp;[^\n]*title=\"&nbsp;\"><\/a>/", '&nbsp;', $this->HTML);
			$this->HTML = preg_replace("/<a [^>]*>(&nbsp;)*<\/a>/", '&nbsp;', $this->HTML);
			$this->HTML = preg_replace("/<%%.*%%>/U", '&nbsp;', $this->HTML);

			if($this->ShowRecordSlots){
				for($j = $i + 1; $j < ($FirstRecord + $this->RecordsPerPage); $j++)
					$this->HTML .= "\n\t<tr><td colspan=".($FieldCountTV+1)."><div class=TableBody>&nbsp;</div></td></tr>";
			}
		// end of data
			$this->HTML.='<!-- tv data above -->';

			if($Print_x == ""){
				$pagesMenu='';
				if($RecordCount > $this->RecordsPerPage){
					$pagesMenu="<td align=center><div class=TableFooter>".$Translation['go to page']." <select onChange=\"$resetSelection document.myform.NoDV.value=1; document.myform.FirstRecord.value=(this.value*".$this->RecordsPerPage."+1); document.myform.submit();\">";
					for($page=0; $page<ceil($RecordCount/$this->RecordsPerPage); $page++){
						$pagesMenu.="<option value=\"$page\" ".($FirstRecord==($page*$this->RecordsPerPage+1)?'selected':'').">".($page+1)."</option>";
					}
					$pagesMenu.='</select></div></td>';
				}
				$totalWidth=array_sum($this->ColWidth);
				$totalWidth=($totalWidth>750?750:'100%');
				$this->HTML .= "\n\t<tr><td colspan=".($FieldCountTV+1)."><table width=100%><tr class=TableFooter><td align=left><input onClick=\"$resetSelection document.myform.NoDV.value=1;\" type=image name=Previous src=previousPage.gif></td><td align=center><div class=TableFooter>" . $Translation["records x to y of z"] . "</div></td>$pagesMenu<td align=right><input onClick=\"$resetSelection document.myform.NoDV.value=1;\" type=image name=Next src=nextPage.gif></td></tr></table></td></tr>";
			}else{
				$this->HTML .= "\n\t<tr><td colspan=".($FieldCountTV+1)."><nobr><div class=TableFooter>" . $Translation["records x to y of z"] . "</div></nobr></td></tr>";
			}
			$this->HTML = str_replace("<FirstRecord>", $FirstRecord, $this->HTML);
			$this->HTML = str_replace("<LastRecord>", $i, $this->HTML);
			$this->HTML = str_replace("<RecordCount>", $RecordCount, $this->HTML);
			$tvShown=true;
		}

	// hidden variables ....
		$this->HTML .= "<input name=SortField value='$SortField' type=hidden>";
		$this->HTML .= "<input name=SelectedID value=\"$SelectedID\" type=hidden>";
		$this->HTML .= "<input name=SelectedField value=\"\" type=hidden>";
		$this->HTML .= "<input name=SortDirection type=hidden value='$SortDirection'>";
		$this->HTML .= "<input name=FirstRecord type=hidden value='$FirstRecord'>";
		$this->HTML .= "<input name=NoDV type=hidden value=''>";
		if($this->QuickSearch && !strpos($this->HTML, 'SearchString')) $this->HTML .= '<input name="SearchString" type="hidden" value="'.htmlspecialchars($SearchString, ENT_QUOTES).'">';
	// hidden variables: filters ...
		$FiltersCode = '';
		for($i = 1; $i <= (20 * $FiltersPerGroup); $i++){ // Number of filters allowed
			if($i%$FiltersPerGroup == 1 && $i != 1 && $FilterAnd[$i]!=""){
				$FiltersCode .= "<input name=\"FilterAnd[$i]\" value=\"$FilterAnd[$i]\" type=\"hidden\">\n";
			}
			if($FilterField[$i] != '' && $FilterOperator[$i] != '' && ($FilterValue[$i] != '' || strstr($FilterOperator[$i], 'Empty'))){
				if(!strstr($FiltersCode, "<input name=\"FilterAnd[$i]\" value="))
					$FiltersCode .= "<input name=\"FilterAnd[$i]\" value=\"$FilterAnd[$i]\" type=\"hidden\">\n";
				$FiltersCode .= "<input name=\"FilterField[$i]\" value=\"$FilterField[$i]\" type=\"hidden\">\n";
				$FiltersCode .= "<input name=\"FilterOperator[$i]\" value=\"$FilterOperator[$i]\" type=\"hidden\">\n";
				$FiltersCode .= "<input name=\"FilterValue[$i]\" value=\"" . htmlspecialchars($FilterValue[$i], ENT_QUOTES) . "\" type=\"hidden\">\n";
			}
		}
		$this->HTML .= $FiltersCode;

	// display details form ...
		if(($this->AllowSelection || $this->AllowInsert || $this->AllowUpdate || $this->AllowDelete) && $Print_x=='' && !$PrintDV){
			if(($this->SeparateDV && $this->HideTableView) || !$this->SeparateDV){
				$dvCode=call_user_func($this->TableName.'_form', $SelectedID, $this->AllowUpdate, (($this->HideTableView && $SelectedID) ? 0 : $this->AllowInsert), $this->AllowDelete, $this->SeparateDV);
				$this->HTML .= "\n\t<tr><td colspan=".($FieldCountTV+2).">$dvCode</td></tr>";
				$this->HTML .= ($this->SeparateDV ? "<input name=SearchString value='".htmlspecialchars($SearchString, ENT_QUOTES)."' type=hidden>" : "");
				if($dvCode){
					$this->ContentType='detailview';
					$dvShown=true;
				}
			}
		}

	// display multiple printable detail views
		if($PrintDV){
			$dvCode='';
			$_POST['dvprint_x']=1;

			// hidden vars
			$this->HTML .= '<input type="hidden" name="Print_x" value="1">'."\n";
			$this->HTML .= '<input type="hidden" name="PrintTV" value="1">'."\n";
			
			// count selected records
			$selectedRecords=0;
			foreach($_POST as $n => $v){
				if(strpos($n, 'select_')===0){
					$id=str_replace('select_', '', $n);
					$selectedRecords++;
					$this->HTML.='<input type="hidden" name="select_'.$id.'" value="1">'."\n";
				}
			}

			if($selectedRecords <= 100){ // if records selected > 100 don't show DV preview to avoid db performance issues.
				foreach($_POST as $n => $v){
					if(strpos($n, 'select_')===0){
						$id=str_replace('select_', '', $n);
						$dvCode.=call_user_func($this->TableName.'_form', $id, 0, 0, 0, 1);
					}
				}
				if($dvCode!=''){
					$dvCode = preg_replace('/<input .*?type="?image"?.*?>/', '', $dvCode);
					$this->HTML .= "\n".'<div class="TableBodySelected displayOnly">'.
										   '<input class="print-button" type="submit" value="'.$Translation['Cancel Printing'].'">'.
										   '<input class="print-button" type="button" id="sendToPrinter" value="'.$Translation['Print'].'" onClick="window.print();">'.
										'</div>'."\n";
					$this->HTML .= $dvCode;
				}
			}else{
				$this->HTML .= '<div class="Error">'.$Translation['Maximum records allowed to enable this feature is'].' 100.</div>';
				$this->HTML .= '<input type="submit" class="print-button" value="'.$Translation['Print Preview Table View'].'">';
			}
		}

		$this->HTML .= "</table>\n";
		if($this->AllowPrintingMultiSelection && $Print_x!='') $this->HTML .= "<script>function toggleAllRecords(){ var s=\$('toggleAll').checked; $toggleAllScript if(s) countSelected=$RecordCount; else countSelected=0; }</script>\n";
		$this->HTML .= "</form></center>";

		if($dvShown && $tvShown) $this->ContentType='tableview+detailview';
		if($dvprint_x!='') $this->ContentType='print-detailview';
		if($Print_x!='') $this->ContentType='print-tableview';

		//mysql_close();
	// Das ist Alles!
	}
}


///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////


class DataCombo{
	var $Query, // Only the first two fields of the query are used.
				// The first field is treated as the primary key (data values),
				// and the second field is the displayed data items.
		$Class,
		$Style,
		$SelectName,
		$FirstItem,     // if not empty, the first item in the combo with value of ""
		$SelectedData,  // a value compared to first field value of the query to select
						// an item from the combo.
		$SelectedText,

		$ListType, // 0: drop down combo, 1: list box, 2: radio buttons
		$ListBoxHeight, // if ListType=1, this is the height of the list box
		$RadiosPerLine, // if ListType=2, this is the number of options per line
		$AllowNull,

		$ItemCount, // this is returned. It indicates the number of items in the combo.
		$HTML,      // this is returned. The combo html source after calling Render().
		$MatchText; // will store the parent caption value of the matching item.

	function DataCombo(){ // Constructor function
		$this->FirstItem = "";
		$this->HTML = "";
		$this->Class = "Option";
		$this->MatchText = "";
		$this->ListType = 0;
		$this->ListBoxHeight=10;
		$this->RadiosPerLine=1;
		$this->AllowNull=1;
	}

	function Render(){
		$result = sql($this->Query);
		$this->ItemCount = mysql_num_rows($result);
		
		$combo=new Combo();
		$combo->Class=$this->Class;
		$combo->Style=$this->Style;
		$combo->SelectName=$this->SelectName;
		$combo->SelectedData=$this->SelectedData;
		$combo->SelectedText=$this->SelectedText;
		$combo->SelectedClass="SelectedOption";
		$combo->ListType=$this->ListType;
		$combo->ListBoxHeight=$this->ListBoxHeight;
		$combo->RadiosPerLine=$this->RadiosPerLine;
		$combo->AllowNull=($this->ListType==2 ? 0 : $this->AllowNull);

		while($row = mysql_fetch_row($result)){
			$combo->ListData[]=htmlspecialchars($row[0], ENT_QUOTES);
			$combo->ListItem[]=$row[1];
		}
		$combo->Render();
		$this->MatchText=$combo->MatchText;
		$this->SelectedText=$combo->SelectedText;
		$this->SelectedData=$combo->SelectedData;
		if($this->ListType==2){
			$this->HTML=str_replace($this->MatchText, $this->MatchText." <%%PLINK($this->SelectName)%%>", $combo->HTML);
		}else{
			$this->HTML=$combo->HTML;
		}
	}
}


///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////


class Combo{
	// The Combo class renders a drop down combo
	// filled with elements in an array ListItem[]
	// and associates each element with data from
	// an array ListData[], and optionally selects 
	// one of the items.

	var $ListItem, // array of items in the combo
		$ListData, // array of items data values
		$Class,
		$SelectedClass,
		$Style,
		$SelectName,
		$SelectID,
		$SelectedData,
		$SelectedText,
		$MatchText, // will store the text value of the matching item.

		$ListType, // 0: drop down combo, 1: list box, 2: radio buttons, 3: multi-selection list box
		$ListBoxHeight, // if ListType=1, this is the height of the list box
		$MultipleSeparator, // if ListType=3, specify the list separator here (default ,)
		$RadiosPerLine, // if ListType=2, this is the number of options per line

		$AllowNull,


		$HTML; // the resulting output HTML code to use

	function Combo(){ // Constructor function
		$this->Class = 'Option';
		$this->SelectedClass = 'SelectedOption';
		$this->HTML = '';
		$this->ListType = 0;
		$this->ListBoxHeight = 10;
		$this->MultipleSeparator = ', ';
		$this->RadiosPerLine = 1;
		$this->AllowNull = true;
	}

	function Render(){
		global $Translation;
		$ArrayCount = count($this->ListItem);

		if($ArrayCount > count($this->ListData)){
			$this->HTML .= 'Invalid Class Definition';
			return 0;
		}

		if(!$this->SelectID)    $this->SelectID=$this->SelectName;

		if($this->ListType!=2){
			$this->HTML .= "<select name=\"$this->SelectName".($this->ListType==3 ? '[]' : '')."\" id=\"$this->SelectID\" class=\"$this->Class\" style=\"$this->Style\"".($this->ListType==1 ? ' size="'.($this->ListBoxHeight < $ArrayCount ? $this->ListBoxHeight : ($ArrayCount + ($this->AllowNull ? 1 : 0))).'"' : '').($this->ListType==3 ? ' multiple' : '').'>';
			$this->HTML .= ($this->AllowNull ? "\n\t<option value=\"\">&nbsp;</option>" : "");

			if($this->ListType==3) $arrSelectedData=explode($this->MultipleSeparator, $this->SelectedData);
			if($this->ListType==3) $arrSelectedText=explode($this->MultipleSeparator, $this->SelectedText);
			for($i = 0; $i < $ArrayCount; $i++){
				if($this->ListType==3){
					if(in_array($this->ListData[$i], $arrSelectedData)){
						$sel = "selected class=\"$this->SelectedClass\"";
						$this->MatchText.=$this->ListItem[$i].$this->MultipleSeparator;
					}else{
						$sel = "class=\"$this->Class\"";
					}
				}else{
					if($this->SelectedData == $this->ListData[$i] || ($this->SelectedText == $this->ListItem[$i] && $this->SelectedText)){
						$sel = "selected class=\"$this->SelectedClass\"";
						$this->MatchText=$this->ListItem[$i];
						$this->SelectedData=$this->ListData[$i];
						$this->SelectedText=$this->ListItem[$i];
					}else{
						$sel = "class=\"$this->Class\"";
					}
				}

				$this->HTML .= "\n\t<option value=\"" . $this->ListData[$i] . "\" $sel>" . htmlspecialchars(stripslashes($this->ListItem[$i])) . "</option>";
			}
			$this->HTML .= "</select>";
			if($this->ListType==3 && strlen($this->MatchText)>0)   $this->MatchText=substr($this->MatchText, 0, -1 * strlen($this->MultipleSeparator));
			if($this->ListType==3) $this->HTML .= '<br />'.$Translation['Hold CTRL key to select multiple items from the above list.'];
		}else{
			global $Translation;
			$separator = '&nbsp; &nbsp; &nbsp; &nbsp;';

			$j=0;
			if($this->AllowNull){
				$this->HTML .= "<input id=\"$this->SelectName$j\" type=\"radio\" name=\"$this->SelectName\" value=\"\" ".($this->SelectedData==''?'checked':'')."> <label for=\"$this->SelectName$j\">{$Translation['none']}</label>";
				$this->HTML .= ($this->RadiosPerLine==1 ? '<br />' : $separator);
				$shift=2;
			}else{
				$shift=1;
			}
			for($i = 0; $i < $ArrayCount; $i++){
				$j++;
				if($this->SelectedData == $this->ListData[$i] || ($this->SelectedText == $this->ListItem[$i] && $this->SelectedText)){
					$sel = "checked class=\"$this->SelectedClass\"";
					$this->MatchText=$this->ListItem[$i];
					$this->SelectedData=$this->ListData[$i];
					$this->SelectedText=$this->ListItem[$i];
				}else{
					$sel = "class=\"$this->Class\"";
				}

				$this->HTML .= "<input id=\"$this->SelectName$j\" type=\"radio\" name=\"$this->SelectName\" value=\"{$this->ListData[$i]}\" $sel> <label for=\"$this->SelectName$j\">".htmlspecialchars(stripslashes($this->ListItem[$i]))."</label>";
				if(($i+$shift)%$this->RadiosPerLine){
					$this->HTML .= $separator;
				}else{
					$this->HTML .= '<br />';
				}
			}
		}

		return 1;
	}
}


///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////


class DateCombo{
	// renders a date combo with a pre-selected date

	var $DateFormat,          // any combination of y,m,d
		$DefaultDate,         // format: yyyy-mm-dd
		$MinYear,
		$MaxYear,
		$MonthNames,
		$Comment,
		$NamePrefix,          // will be used in the HTML name prop as a prefix to "Year", "Month", "Day"
		$RTL,
		$CSSOptionClass,
		$CSSSelectedClass,
		$CSSCommentClass;

	function DateCombo(){
		// set default values
		$this->DateFormat = "ymd";
		$this->DefaultDate = '';
		$this->MinYear = 1900;
		$this->MaxYear = 2100;
		$this->MonthNames = "January,February,March,April,May,June,July,August,September,October,November,December";
		$this->Comment = "<empty>";
		$this->NamePrefix = "Date";

		$this->RTL = 0;
		$this->CSSOptionClass = "";
		$this->CSSSelectedClass = "";
		$this->CSSCommentClass = "";
	}

	function GetHTML($readOnly=false){
		list($xy, $xm, $xd)=explode('-', $this->DefaultDate);

		//$y : render years combo
		$years = new Combo;
		for($i=$this->MinYear; $i<=$this->MaxYear; $i++){
			$years->ListItem[] = $i;
			$years->ListData[] = $i;
		}
		$years->SelectName = $this->NamePrefix . 'Year';
		$years->SelectID = $this->NamePrefix;
		$years->SelectedData = $xy;
		$years->Class = $this->CSSOptionClass;
		$years->SelectedClass = $this->CSSSelectedClass;
		$years->Render();
		$y = ($readOnly ? substr($this->DefaultDate, 0, 4) : $years->HTML);

		//$m : render months combo
		$months = new Combo;
		for($i=1; $i<=12; $i++){
			$months->ListData[] = $i;
		}
		$months->ListItem = explode(",", $this->MonthNames);
		$months->SelectName = $this->NamePrefix . 'Month';
		$months->SelectID = $this->NamePrefix . '-mm';
		$months->SelectedData = intval($xm);
		$months->Class = $this->CSSOptionClass;
		$months->SelectedClass = $this->CSSSelectedClass;
		$months->Render();
		$m = ($readOnly ? $xm : $months->HTML);

		//$d : render days combo
		$days = new Combo;
		for($i=1; $i<=31; $i++){
			$days->ListItem[] = $i;
			$days->ListData[] = $i;
		}
		$days->SelectName = $this->NamePrefix . 'Day';
		$days->SelectID = $this->NamePrefix . '-dd';
		$days->SelectedData = intval($xd);
		$days->Class = $this->CSSOptionClass;
		$days->SelectedClass = $this->CSSSelectedClass;
		$days->Render();
		$d = ($readOnly ? $xd : $days->HTML);

		$p1 = substr($this->DateFormat, 0, 1);
		$p2 = substr($this->DateFormat, 1, 1);
		$p3 = substr($this->DateFormat, 2, 1);

		return ($readOnly ? "${$p1}/${$p2}/${$p3}" : "${$p1} / ${$p2} / ${$p3}");
	}
}

///////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////

function toMySQLDate($formattedDate, $sep='/', $ord='mdY'){
	// extract date elements
	$de=explode($sep, $formattedDate);
	$mySQLDate=intval($de[strpos($ord, 'Y')]).'-'.intval($de[strpos($ord, 'm')]).'-'.intval($de[strpos($ord, 'd')]);
	return $mySQLDate;
}

function highlight($needle, $haystack){
	$needle = preg_quote($needle, "/");
	return preg_replace("#(?!<.*?)(".$needle.")(?![^<>]*?>)#i", '<span style="background-color: #FFFF00;">\1</span>', $haystack);
}

function reIndex(&$arr){
	/*	returns a copy of the given array,
		with keys replaced by 1-based numeric indices,
		and values replaced by original keys
	*/
	$i=1;
	foreach($arr as $n=>$v){
		$arr2[$i]=$n;
		$i++;
	}
	return $arr2;
}

?>