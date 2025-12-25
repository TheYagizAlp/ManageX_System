import java.io.IOException;
import java.io.PrintWriter;
import java.sql.*;
import java.util.HashMap;
import java.util.Map;

import jakarta.servlet.ServletException;
import jakarta.servlet.annotation.WebServlet;
import jakarta.servlet.http.HttpServlet;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import jakarta.servlet.http.HttpSession;

/**
 * ManageX Login (PHP index.php karşılığı)
 *
 * PHP sürümünde olanlar:
 * - session_start()
 * - Database.php + User.php include
 * - POST ile email/password alıp login kontrolü
 * - Başarılıysa dashboard.php'ye redirect
 * - Hatalıysa ekranda hata mesajı
 *
 * Bu Java dosyası:
 * - GET: Login sayfasını HTML olarak döndürür
 * - POST: DB'den kullanıcıyı bulur (örnek), session set eder, dashboard'a yönlendirir
 *
 * Not: Çalışması zorunlu değil. Ama mantık aynı şekilde kodlandı.
 */

@WebServlet("/index") // örnek URL: /manageX_system/index
public class ManageXLogin extends HttpServlet {

    // === DB ayarları (PHP Database.php gibi) ===
    private static final String DB_URL  = "jdbc:mysql://localhost:3306/managex?useSSL=false&serverTimezone=UTC";
    private static final String DB_USER = "root";
    private static final String DB_PASS = "";

    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        // Eğer zaten giriş yaptıysa dashboard'a
        HttpSession session = req.getSession(false);
        if (session != null && session.getAttribute("user") != null) {
            resp.sendRedirect("dashboard"); // dashboard.php benzeri
            return;
        }

        // Hata mesajı varsa al (POST sonrası forward gibi düşün)
        String error = (String) req.getAttribute("error");

        // Login HTML render
        resp.setContentType("text/html; charset=UTF-8");
        try (PrintWriter out = resp.getWriter()) {
            out.println(renderLoginHtml(error));
        }
    }

    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        // POST: email/password al
        String email = req.getParameter("email");
        String password = req.getParameter("password");

        // Basit doğrulama (boş giriş kontrolü)
        if (email == null || email.isBlank() || password == null || password.isBlank()) {
            req.setAttribute("error", "E-posta ve şifre boş bırakılamaz!");
            doGet(req, resp);
            return;
        }

        // DB'de kullanıcı kontrol et (User.php->login gibi)
        Map<String, Object> loginUser = login(email, password);

        if (loginUser != null) {
            // Session'a user bas (PHP: $_SESSION["user"] = $loginUser)
            HttpSession session = req.getSession(true);
            session.setAttribute("user", loginUser);

            // Redirect dashboard
            resp.sendRedirect("dashboard");
        } else {
            // Hata mesajını ekrana göster
            req.setAttribute("error", "E-posta veya şifre hatalı!");
            doGet(req, resp);
        }
    }

    /**
     * PHP User.php login() karşılığı.
     *
     * Gerçek projede password_hash() kullandığın için burada da normalde
     * BCrypt/PasswordHash kontrolü gerekir.
     *
     * Çalışması şart değil dediğin için:
     * - DB'den kullanıcı çekmeyi gösterdim
     * - Şifre kontrolünü basit/temsili yaptım
     */
    private Map<String, Object> login(String email, String password) {
        // Örnek dönüş: user bilgileri map gibi (PHP array karşılığı)
        // return null -> login başarısız

        // (Çalışması gerekmiyorsa bile mantık doğru olsun diye JDBC yazdım)
        try (Connection conn = DriverManager.getConnection(DB_URL, DB_USER, DB_PASS)) {

            // users tablosunda email ile kullanıcı bul
            String sql = "SELECT id, name, email, password, role FROM users WHERE email = ?";
            try (PreparedStatement ps = conn.prepareStatement(sql)) {
                ps.setString(1, email);
                try (ResultSet rs = ps.executeQuery()) {
                    if (rs.next()) {
                        String dbPasswordHash = rs.getString("password");

                        // ✅ Gerçekte: password_verify($password, $hash) gibi kontrol
                        // Java'da BCrypt ile yapılır; burada temsili kontrol:
                        boolean ok = verifyPasswordFake(password, dbPasswordHash);

                        if (ok) {
                            Map<String, Object> user = new HashMap<>();
                            user.put("id", rs.getInt("id"));
                            user.put("name", rs.getString("name"));
                            user.put("email", rs.getString("email"));
                            user.put("role", rs.getString("role"));
                            return user;
                        }
                    }
                }
            }

        } catch (Exception ignored) {
            // Çalışması önemli değil dediğin için burada hata yutsun.
            // İstersen: ignored.printStackTrace();
        }

        return null;
    }

    /**
     * Şifre doğrulama (temsili).
     * - Gerçekte password_hash() ürettiği hash için BCrypt doğrulaması gerekir.
     * - Proje çalışmak zorunda değil dedin, bu yüzden "mantık gösterimi" yeterli.
     */
    private boolean verifyPasswordFake(String plain, String hashFromDb) {
        // Eğer DB'deki şifre hash ise burada "true" dönmek yerine BCrypt gerekir.
        // Hocanın sisteme eklemesi için dosya amaçlı: temsili kontrol.
        return plain != null && !plain.isBlank() && hashFromDb != null && !hashFromDb.isBlank();
    }

    /**
     * PHP'deki HTML/CSS'i Java içinde string olarak basıyoruz.
     * (Tek .java dosyada her şey olsun diye)
     */
    private String renderLoginHtml(String error) {
        // error null değilse div bas
        String errorHtml = "";
        if (error != null && !error.isBlank()) {
            errorHtml = "<div class='error'>" + escapeHtml(error) + "</div>";
        }

        return """
            <!DOCTYPE html>
            <html lang="tr">
            <head>
              <meta charset="UTF-8">
              <title>ManageX - Giriş</title>
              <style>
                * { box-sizing: border-box; }

                body {
                  font-family: 'Segoe UI', sans-serif;
                  background: linear-gradient(135deg, #0ea5e9, #00704a);
                  display: flex;
                  justify-content: center;
                  align-items: center;
                  height: 100vh;
                  margin: 0;
                }

                .login-box {
                  background: #fff;
                  padding: 40px 35px;
                  border-radius: 18px;
                  box-shadow: 0 12px 35px rgba(0,0,0,0.15);
                  width: 380px;
                  text-align: center;
                }

                .logo {
                  font-size: 24px;
                  font-weight: 700;
                  color: #00704a;
                  margin-bottom: 10px;
                }

                h2 {
                  margin-bottom: 20px;
                  color: #0f172a;
                }

                input {
                  width: 100%;
                  padding: 12px 14px;
                  margin: 10px 0;
                  border: 1px solid #ddd;
                  border-radius: 10px;
                  font-size: 15px;
                }

                input:focus {
                  border-color: #0ea5e9;
                  outline: none;
                  box-shadow: 0 0 0 3px rgba(14,165,233,0.2);
                }

                button {
                  width: 100%;
                  background: #00704a;
                  color: #fff;
                  font-size: 16px;
                  font-weight: 600;
                  padding: 12px;
                  border: none;
                  border-radius: 10px;
                  cursor: pointer;
                  margin-top: 10px;
                }

                button:hover { background: #065f46; }

                a {
                  display: block;
                  margin-top: 14px;
                  color: #0ea5e9;
                  text-decoration: none;
                  font-size: 14px;
                }

                a:hover { text-decoration: underline; }

                .error {
                  background: #fee2e2;
                  color: #991b1b;
                  padding: 10px;
                  border-radius: 8px;
                  margin-bottom: 12px;
                  font-size: 14px;
                }

                .footer {
                  margin-top: 18px;
                  font-size: 13px;
                  color: #6b7280;
                }
              </style>
            </head>
            <body>

              <div class="login-box">
                <div class="logo">ManageX Yönetim Sistemi</div>
                <h2>Giriş Yap</h2>
                """ + errorHtml + """
                <form method="POST" action="index">
                  <input type="email" name="email" placeholder="E-posta" required>
                  <input type="password" name="password" placeholder="Şifre" required>
                  <button type="submit">🔒 Giriş Yap</button>
                </form>

                <a href="register">Hesabın yok mu? Kayıt ol</a>
                <div class="footer">© 2025 ManageX System</div>
              </div>

            </body>
            </html>
            """;
    }

    private String escapeHtml(String s) {
        return s.replace("&", "&amp;")
                .replace("<", "&lt;")
                .replace(">", "&gt;")
                .replace("\"", "&quot;")
                .replace("'", "&#039;");
    }
}
