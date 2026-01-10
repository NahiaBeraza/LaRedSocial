package libreriavirtual.servlets;

import java.io.IOException;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;

import libreriavirtual.bd.UsuarioBd;
import libreriavirtual.beans.Usuario;
import libreriavirtual.config.GestorConfiguracion;

@WebServlet("/SrvValidarEntrada")
public class SrvValidarEntrada extends HttpServlet {
    private static final long serialVersionUID = 1L;

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
    	GestorConfiguracion.cargarConfiguracion();


        // Recoger datos del formulario
        String usuario = request.getParameter("usuario");
        String clave = request.getParameter("clave");

        UsuarioBd bd = new UsuarioBd();
        boolean correcto = false;
        Usuario u = null;

        // Validar usuario en BD
        if (bd.abrirConexion()) {
            correcto = bd.validarUsuario(usuario, clave);

            // Si es correcto, obtener el objeto Usuario completo
            if (correcto) {
                u = bd.obtenerUsuarioPorLogin(usuario, clave);
            }

            bd.cerrarConexion();
        }

        // Si es correcto crear sesión y enviar a menú
        if (correcto && u != null) {
            HttpSession sesion = request.getSession(true);
            sesion.setAttribute("usuarioObj", u);  // Guardamos el objeto completo
            request.getRequestDispatcher("menu.jsp").forward(request, response);
        } 
        // Si NO existe enviar a registro
        else {
            response.sendRedirect("registro_usuario.jsp");
        }
    }
}
