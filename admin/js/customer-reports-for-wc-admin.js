jQuery(document).ready(function($) { 
    $('#woo_customer_info').DataTable({
    	"info": false,
    	"ordering": true,
	    "columnDefs": [
		    {
		      orderable: false,
		      targets: "no-sort"
		    },
		    { 
		    	"searchable": false, 
		    	"targets": [1, 3, 4, 5, 6, 7] 
			}
		],
		"dom": 'Bfrtip',
        "buttons": [
            {
				extend: 'csvHtml5',
				text: 'Download Report'
			}
        ]
    });


});