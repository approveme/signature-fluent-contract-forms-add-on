var url = String(window.location);
var hasEsig = url.substr(url.length - 12);

if(hasEsig == 'wpesignature'){

    document.getElementsByClassName('el-dropdown-menu')[0].setAttribute('style','display:none !important');


    
}
