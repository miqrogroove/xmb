function add() {
    var to   = opener.document.getElementById('msgto');
    var from = document.getElementsByName('users');
    var add  = new Array();
    var j    = 0;

    if (from.length > 0) {
        for(var i=0; i<from.length; i++) {
            if (from[i].checked == 1) {
                add[j++] = from[i].value;
            }
        }
    }

    if (to.value != '') {
        old = to.value.split(', ');
        for(i=0;i<old.length;i++) {
            for(j=0;j<add.length;j++) {
                if (add[j] == old[i]) {
                    add.splice(j,1);
                    break;
                }
            }
        }

        if (add.length > 0) {
            to.value += ', '+add.join(', ');
        }
    } else {
        to.value = add.join(', ');
    }
    opener.aBookOpen = false;
    self.close();
}