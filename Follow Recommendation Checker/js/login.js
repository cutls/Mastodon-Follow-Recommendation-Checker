function instance(red) {
    var url = $("#url").val()
    login(url);
  }
  
  function login(url) {
    var elem = document.getElementById('mess');
    elem.textContent = 'Please Wait...';
    var red = location.href;
    if (!red.match(/index\.php/)) {
      red = red + "index.php";
    }
    console.log(red);
    var start = "https://" + url + "/api/v1/apps";
    fetch(start, {
      method: 'POST',
      headers: {
        'content-type': 'application/json'
      },
      body: JSON.stringify({
        scopes: 'read',
        client_name: "Follow Recommendation Checker",
        redirect_uris: red
      })
    }).then(function (response) {
      return response.json();
    }).catch(function (error) {
      console.error(error);
    }).then(function (json) {
      localStorage.setItem("last", url);
      var auth = "https://" + url + "/oauth/authorize?client_id=" + json["client_id"] + "&client_secret=" + json["client_secret"] + "&response_type=code&redirect_uri=" + red + "&scope=read&state=" + url + "+" + json["client_id"] + "+" + json["client_secret"];
      location.href = auth;
    });
  }
  
  function load(red) {
    var last = localStorage.getItem("last");
    if (last) {
      $("#suggest").html('最後に使ったインスタンス:<a onclick="login(\'' + last + '\',\'' + red + '\',)" style="cursor:pointer;"><u>' + last + '</u></a>');
    }
  }
  $('#login').on('click', function () {
    instance()
  });