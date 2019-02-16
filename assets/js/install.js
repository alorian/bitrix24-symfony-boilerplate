require('../css/install.css');

BX24.init(function () {
    var result = document.getElementById("result");
    if(result.className === 'success') {
        BX24.installFinish();
    }
});