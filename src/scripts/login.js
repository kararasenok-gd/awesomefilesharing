var captchaToken = "";

var regBTN = document.getElementById("registerBtn");
var loginBTN = document.getElementById("loginBtn");
var regForm = document.getElementById("registerForm");
var loginForm = document.getElementById("loginForm");
var regUsername = document.getElementById("regUsername");
var regPassword = document.getElementById("regPassword");
var regEmail = document.getElementById("regEmail");
var loginUsername = document.getElementById("loginUsername");
var loginPassword = document.getElementById("loginPassword");

function onCaptchaSuccess(token) {
    captchaToken = token;

    regBTN.disabled = false;
    loginBTN.disabled = false;

    console.log('hCaptcha token:', captchaToken);
}

function resetCaptcha() {
    regBTN.disabled = true;
    loginBTN.disabled = true;
    captchaToken = "";

    hcaptcha.reset();
}


regBTN.addEventListener("click", function() {
    if (regUsername.value && regPassword.value && regEmail.value && captchaToken) {
        const fd = new FormData();
        fd.append("username", regUsername.value);
        fd.append("password", regPassword.value);
        fd.append("email", regEmail.value);
        fd.append("hcaptcha", captchaToken);

        fetch("../api/sys/register.php", {
            method: "POST",
            body: fd
        }).then(res => res.json()).then(data => {
            resetCaptcha();
            if (data.success) {
                if (data.message && data.message == "Verification email sent") {
                    alert("Письмо с подтверждением отправлено на почту!\nЕсли письма долго нет, то проверьте спам");
                } else {
                    alert("Вы успешно зарегистрировались!");
                    window.location.href = "../";
                }
            } else {
                alert(data.error);
            }
        });
    }
});


loginBTN.addEventListener("click", function() {
    if (loginUsername.value && loginPassword.value && captchaToken) {
        const fd = new FormData();
        fd.append("username", loginUsername.value);
        fd.append("password", loginPassword.value);
        fd.append("hcaptcha", captchaToken);

        fetch("../api/sys/login.php", {
            method: "POST",
            body: fd
        }).then(res => res.json()).then(data => {
            resetCaptcha();
            if (data.success) {
                alert("Вы успешно вошли!");
                window.location.href = "../";
            } else {
                alert(data.error);
            }
        });
    }
});


function handleMessageInParams() {
    const params = new URLSearchParams(window.location.search);
    if (params.has("msg")) {
        document.getElementById("notificationText").textContent = params.get("msg");

        // play animation
        const ntfc = document.getElementById("notification")
        ntfc.classList.toggle("show");
        ntfc.classList.toggle("hide");

        const url = new URL(window.location.href);
        url.searchParams.delete("msg");
        window.history.replaceState(null, null, url.href);

        setTimeout(() => {
            ntfc.classList.toggle("show");
            ntfc.classList.toggle("hide");
        }, 15000);
    }
}

handleMessageInParams();