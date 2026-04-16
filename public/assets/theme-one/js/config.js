var primary = localStorage.getItem("primary") || '#24695c';
var secondary = localStorage.getItem("secondary") || '#ba895d';

window.vihoAdminConfig = {
	// Theme Primary Color
	primary: primary,
	// theme secondary color
	secondary: secondary,
};

if (localStorage.getItem('page-wrapper') === null) {
    localStorage.setItem('page-wrapper', 'compact-wrapper modern-sidebar');
}

if (localStorage.getItem('page-body-wrapper') === null) {
    localStorage.setItem('page-body-wrapper', 'sidebar-icon');
}

var storedPageWrapper = localStorage.getItem('page-wrapper') || '';
if (storedPageWrapper.indexOf('compact-wrapper') === -1) {
    localStorage.setItem('page-wrapper', 'compact-wrapper modern-sidebar');
}

var storedBodyWrapper = localStorage.getItem('page-body-wrapper') || '';
if (storedBodyWrapper !== 'sidebar-icon') {
    localStorage.setItem('page-body-wrapper', 'sidebar-icon');
}





// defalt layout
$("#default-demo").click(function(){      
    localStorage.setItem('page-wrapper', 'compact-wrapper modern-sidebar');
    localStorage.setItem('page-body-wrapper', 'sidebar-icon');
});


// compact layout
$("#compact-demo").click(function(){   
    localStorage.setItem('page-wrapper', 'compact-wrapper compact-sidebar');
    localStorage.setItem('page-body-wrapper', 'sidebar-icon');
});



// modern layout
$("#modern-demo").click(function(){   
    localStorage.setItem('page-wrapper', 'compact-wrapper modern-sidebar');
    localStorage.setItem('page-body-wrapper', 'sidebar-icon');
});