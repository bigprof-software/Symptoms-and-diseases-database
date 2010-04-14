var FiltersEnabled = 0; // if your not going to use transitions or filters in any of the tips set this to 0
var spacer="&nbsp; &nbsp; &nbsp; ";

// email notifications to admin
notifyAdminNewMembers0Tip=["", spacer+"No email notifications to admin."];
notifyAdminNewMembers1Tip=["", spacer+"Notify admin only when a new member is waiting for approval."];
notifyAdminNewMembers2Tip=["", spacer+"Notify admin for all new sign-ups."];

// visitorSignup
visitorSignup0Tip=["", spacer+"If this option is selected, visitors will not be able to join this group unless the admin manually moves them to this group from the admin area."];
visitorSignup1Tip=["", spacer+"If this option is selected, visitors can join this group but will not be able to sign in unless the admin approves them from the admin area."];
visitorSignup2Tip=["", spacer+"If this option is selected, visitors can join this group and will be able to sign in instantly with no need for admin approval."];

// diseases table
diseases_addTip=["",spacer+"This option allows all members of the group to add records to the 'Diseases' table. A member who adds a record to the table becomes the 'owner' of that record."];

diseases_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Diseases' table."];
diseases_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Diseases' table."];
diseases_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Diseases' table."];
diseases_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Diseases' table."];

diseases_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Diseases' table."];
diseases_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Diseases' table."];
diseases_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Diseases' table."];
diseases_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Diseases' table, regardless of their owner."];

diseases_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Diseases' table."];
diseases_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Diseases' table."];
diseases_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Diseases' table."];
diseases_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Diseases' table."];

// patients table
patients_addTip=["",spacer+"This option allows all members of the group to add records to the 'Patients' table. A member who adds a record to the table becomes the 'owner' of that record."];

patients_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Patients' table."];
patients_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Patients' table."];
patients_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Patients' table."];
patients_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Patients' table."];

patients_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Patients' table."];
patients_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Patients' table."];
patients_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Patients' table."];
patients_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Patients' table, regardless of their owner."];

patients_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Patients' table."];
patients_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Patients' table."];
patients_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Patients' table."];
patients_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Patients' table."];

// symptoms table
symptoms_addTip=["",spacer+"This option allows all members of the group to add records to the 'Symptoms' table. A member who adds a record to the table becomes the 'owner' of that record."];

symptoms_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Symptoms' table."];
symptoms_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Symptoms' table."];
symptoms_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Symptoms' table."];
symptoms_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Symptoms' table."];

symptoms_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Symptoms' table."];
symptoms_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Symptoms' table."];
symptoms_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Symptoms' table."];
symptoms_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Symptoms' table, regardless of their owner."];

symptoms_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Symptoms' table."];
symptoms_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Symptoms' table."];
symptoms_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Symptoms' table."];
symptoms_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Symptoms' table."];

// disease_symptoms table
disease_symptoms_addTip=["",spacer+"This option allows all members of the group to add records to the 'Disease symptoms' table. A member who adds a record to the table becomes the 'owner' of that record."];

disease_symptoms_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Disease symptoms' table."];
disease_symptoms_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Disease symptoms' table."];
disease_symptoms_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Disease symptoms' table."];
disease_symptoms_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Disease symptoms' table."];

disease_symptoms_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Disease symptoms' table."];
disease_symptoms_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Disease symptoms' table."];
disease_symptoms_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Disease symptoms' table."];
disease_symptoms_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Disease symptoms' table, regardless of their owner."];

disease_symptoms_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Disease symptoms' table."];
disease_symptoms_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Disease symptoms' table."];
disease_symptoms_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Disease symptoms' table."];
disease_symptoms_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Disease symptoms' table."];

// patient_symptoms table
patient_symptoms_addTip=["",spacer+"This option allows all members of the group to add records to the 'Patient symptoms' table. A member who adds a record to the table becomes the 'owner' of that record."];

patient_symptoms_view0Tip=["",spacer+"This option prohibits all members of the group from viewing any record in the 'Patient symptoms' table."];
patient_symptoms_view1Tip=["",spacer+"This option allows each member of the group to view only his own records in the 'Patient symptoms' table."];
patient_symptoms_view2Tip=["",spacer+"This option allows each member of the group to view any record owned by any member of the group in the 'Patient symptoms' table."];
patient_symptoms_view3Tip=["",spacer+"This option allows each member of the group to view all records in the 'Patient symptoms' table."];

patient_symptoms_edit0Tip=["",spacer+"This option prohibits all members of the group from modifying any record in the 'Patient symptoms' table."];
patient_symptoms_edit1Tip=["",spacer+"This option allows each member of the group to edit only his own records in the 'Patient symptoms' table."];
patient_symptoms_edit2Tip=["",spacer+"This option allows each member of the group to edit any record owned by any member of the group in the 'Patient symptoms' table."];
patient_symptoms_edit3Tip=["",spacer+"This option allows each member of the group to edit any records in the 'Patient symptoms' table, regardless of their owner."];

patient_symptoms_delete0Tip=["",spacer+"This option prohibits all members of the group from deleting any record in the 'Patient symptoms' table."];
patient_symptoms_delete1Tip=["",spacer+"This option allows each member of the group to delete only his own records in the 'Patient symptoms' table."];
patient_symptoms_delete2Tip=["",spacer+"This option allows each member of the group to delete any record owned by any member of the group in the 'Patient symptoms' table."];
patient_symptoms_delete3Tip=["",spacer+"This option allows each member of the group to delete any records in the 'Patient symptoms' table."];

/*
	Style syntax:
	-------------
	[TitleColor,TextColor,TitleBgColor,TextBgColor,TitleBgImag,TextBgImag,TitleTextAlign,
	TextTextAlign,TitleFontFace,TextFontFace, TipPosition, StickyStyle, TitleFontSize,
	TextFontSize, Width, Height, BorderSize, PadTextArea, CoordinateX , CoordinateY,
	TransitionNumber, TransitionDuration, TransparencyLevel ,ShadowType, ShadowColor]

*/

toolTipStyle=["white","#00008B","#000099","#E6E6FA","","images/helpBg.gif","","","","\"Trebuchet MS\", sans-serif","","","","3",400,"",1,2,10,10,51,1,0,"",""];

applyCssFilter();
