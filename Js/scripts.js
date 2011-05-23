
function emailCheck(emailStr) {
    var checkTLD=1;
    var knownDomsPat=/^(com|net|org|edu|int|mil|gov|arpa|biz|aero|name|coop|info|pro|museum)$/;
    var emailPat=/^(.+)@(.+)$/;
    var specialChars="\\(\\)><@,;:\\\\\\\"\\.\\[\\]";
    var validChars="\[^\\s" + specialChars + "\]";
    var quotedUser="(\"[^\"]*\")";
    var ipDomainPat=/^\[(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\]$/;
    var atom=validChars + '+';
    var word="(" + atom + "|" + quotedUser + ")";
    var userPat=new RegExp("^" + word + "(\\." + word + ")*$");
    var domainPat=new RegExp("^" + atom + "(\\." + atom +")*$");
    var matchArray=emailStr.match(emailPat);
    if (matchArray==null) {
        return "Email address seems incorrect (check @ and .'s)";
    }
    var user=matchArray[1];
    var domain=matchArray[2];
    for (i=0; i<user.length; i++) {
        if (user.charCodeAt(i)>127) {
            return "Ths username contains invalid characters.";
        }
    }
    for (i=0; i<domain.length; i++) {
        if (domain.charCodeAt(i)>127) {
            return "Ths domain name contains invalid characters.";
        }
    }
    if (user.match(userPat)==null) {
        return "The username doesn't seem to be valid.";
    }
    var IPArray=domain.match(ipDomainPat);
    if (IPArray!=null) {

        // this is an IP address

        for (var i=1;i<=4;i++) {
            if (IPArray[i]>255) {
                return "Destination IP address is invalid!";
            }
        }
        return true;
    }
    var atomPat=new RegExp("^" + atom + "$");
    var domArr=domain.split(".");
    var len=domArr.length;
    for (i=0;i<len;i++) {
        if (domArr[i].search(atomPat)==-1) {
            return "The domain name does not seem to be valid.";
        }
    }
    if (checkTLD && domArr[domArr.length-1].length!=2 &&
        domArr[domArr.length-1].search(knownDomsPat)==-1) {
        return "The address must end in a well-known domain or two letter " + "country.";
    }
    if (len<2) {
        return "This address is missing a hostname!";
    }
    return "1";
}


function trim(str){
    if (!str || str == '') return '';
    return str.replace(new RegExp('^'+String.fromCharCode(92)+'s*'),'').replace(new RegExp(String.fromCharCode(92)+'s*'+String.fromCharCode(36)),'');
}
