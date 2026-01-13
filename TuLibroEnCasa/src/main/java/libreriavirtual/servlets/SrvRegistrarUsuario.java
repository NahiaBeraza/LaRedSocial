package libreriavirtual.servlets;

import java.io.IOException;
import java.sql.Date;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;

import libreriavirtual.bd.UsuarioBd;
import libreriavirtual.beans.Usuario;
import libreriavirtual.config.GestorConfiguracion;

@WebServlet("/SrvRegistrarUsuario")
public class SrvRegistrarUsuario extends HttpServlet {
    private static final long serialVersionUID = 1L;

    @Override
    protected void doPost(HttpServletRequest request, HttpServletResponse response)
            throws ServletException, IOException {
    	
    	GestorConfiguracion.cargarConfiguracion();


        String nombre = request.getParameter("nombre");
        String apellido1 = request.getParameter("apellido1");
        String apellido2 = request.getParameter("apellido2");
        String dni = request.getParameter("dni");
        String direccion = request.getParameter("direccion");
        String fecha = request.getParameter("fecha_nacimiento");
        String email = request.getParameter("email");
        String usuario = request.getParameter("usuario");
        String clave = request.getParameter("clave");
        String confirmar = request.getParameter("confirmar_clave");

        //Validar claves
        if (!clave.equals(confirmar)) {
            request.setAttribute("error", "Las contraseñas no coinciden");
            request.getRequestDispatcher("registro_usuario.jsp").forward(request, response);
            return;
        }

        UsuarioBd bd = new UsuarioBd();

        //Comprobar si el usuario ya existe (solo por nombre de usuario)
        boolean existe = false;
        if (bd.abrirConexion()) {
        	//System.out.println("DEBUG conexión antes de insertar = " + bd.conexion);

            existe = bd.existeUsuario(usuario); // mejor que validarUsuario()
            bd.cerrarConexion();
        }

        if (existe) {
            request.setAttribute("error", "El usuario ya existe");
            request.getRequestDispatcher("registro_usuario.jsp").forward(request, response);
            return;
        }

        //Construir el objeto Usuario
        Usuario u = new Usuario();
        u.setNombre(nombre);
        u.setApellido1(apellido1);
        u.setApellido2(apellido2);
        u.setDni(dni);
        u.setDireccion(direccion);

        //Convertir fecha si viene vacía
        if (fecha != null && !fecha.isEmpty()) {
            u.setFechaNacimiento(Date.valueOf(fecha)); // java.sql.Date
        } else {
            u.setFechaNacimiento(null);
        }

        u.setEmail(email);
        u.setUsuario(usuario);
        u.setClave(clave);

        //Insertar usuario
        boolean insertado = false;
        if (bd.abrirConexion()) {
            insertado = bd.insertarUsuario(u);
            bd.cerrarConexion();
        }

        if (!insertado) {
            request.setAttribute("error", "No se pudo registrar el usuario");
            request.getRequestDispatcher("registro_usuario.jsp").forward(request, response);
            return;
        }

        //Crear sesión
        HttpSession sesion = request.getSession(true);//SI NO EXISTE LA SESION ME LA CREA,por eso ponemos el true
        //sesion.setAttribute("usuario", usuario); en lugar de guardar solo el nombre vamos a poner o objeto usuario y poder acceder a sus atributos (nombre y apellidos para mostrarlos en pantalla)
        sesion.setAttribute("usuarioObj", u);

        //Ir al menú
        response.sendRedirect("menu.jsp");
    }
}

