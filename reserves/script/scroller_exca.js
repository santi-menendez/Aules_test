var caldef1 = {
		firstday:1,     // First day of the week: 0 means Sunday, 1 means Monday, etc.
		dtype:'dd/MM/yyyy', // Output date format MM-month, dd-date, yyyy-year, HH-hours, mm-minutes, ss-seconds
		width:250,       // Width of the calendar table
		windoww:300,     // Width of the calendar window
		windowh:200,     // Height of the calendar window
		border_width:0,      // Border of the table
		border_color:'#0000d3',  // Color of the border
		dn_css:'clsDayName',     // CSS for week day names
		cd_css:'clsCurrentDay',  // CSS for current day
		wd_css:'clsWorkDay',     // CSS for work days (this month)
		we_css:'clsWeekEnd',     // CSS for weekend days (this month)
		wdom_css:'clsWorkDayOtherMonth', // CSS for work days (other month)
		weom_css:'clsWeekEndOtherMonth', // CSS for weekend days (other month)
		headerstyle: {
			type:'buttons',         // Type of the header may be: 'buttons' or 'comboboxes'
			css:'clsDayName',       // CSS for header
			imgnextm:'/intrafnb/aules/reserves/img/next.gif', // Image for next month button.
			imgprevm:'/intrafnb/aules/reserves/img/prev.gif',    // Image for previous month button.
			imgnexty:'/intrafnb/aules/reserves/img/next_year.gif', // Image for next year button.
			imgprevy:'/intrafnb/aules/reserves/img/prev_year.gif'     // Image for previous year button.
		},
				template_path:'/web/public_html_new/intranet/aules/reserves',
		// Array with month names
		monthnames :["Gener", "Febrer", "Mar&ccedil;", "Abril", "Maig", "Juny", "Juliol", "Agost", "Setembre", "Octubre", "Novembre", "Desembre"],
		// Array with week day names
		daynames : ["Dg", "Dl", "Dt", "Dc", "Dj", "Dv", "Ds"]
};
