package libreriavirtual.bd;

import java.sql.PreparedStatement;
import java.sql.ResultSet;

import libreriavirtual.beans.Usuario;

public class UsuarioBd extends BdBase {

    public UsuarioBd() {
        super();
    }

    /**
     * Valida si existe un usuario con ese usuario + clave
     */
    public boolean validarUsuario(String user, String clave) {
        boolean correcto = false;

        try {            
            String sql = "SELECT * FROM Usuario WHERE usuario = ? AND clave = ?";
            PreparedStatement ps = conexion.prepareStatement(sql);
            ps.setString(1, user);
            ps.setString(2, clave);

            ResultSet rs = ps.executeQuery();
            correcto = rs.next();

            rs.close();
            ps.close();

        } catch (Exception e) {
            e.printStackTrace();
            System.out.println("Validación de usuario no efectuada correctamente");
            correcto = false;
        }

        return correcto;
    }

    /**
     * Devuelve un objeto Usuario completo si el login es correcto
     */
    public Usuario obtenerUsuarioPorLogin(String user, String clave) {
        Usuario u = null;

        try {            
            String sql = "SELECT * FROM Usuario WHERE usuario = ? AND clave = ?";
            PreparedStatement ps = conexion.prepareStatement(sql);
            ps.setString(1, user);
            ps.setString(2, clave);

            ResultSet rs = ps.executeQuery();

            if (rs.next()) {
                u = new Usuario();
                u.setIdUsuario(rs.getInt("id_usuario"));
                u.setNombre(rs.getString("nombre"));
                u.setApellido1(rs.getString("apellido1"));
                u.setApellido2(rs.getString("apellido2"));
                u.setDni(rs.getString("dni"));
                u.setDireccion(rs.getString("direccion"));
                u.setFechaNacimiento(rs.getDate("fecha_nacimiento"));
                u.setEmail(rs.getString("email"));
                u.setUsuario(rs.getString("usuario"));
                u.setClave(rs.getString("clave"));
            }

            rs.close();
            ps.close();              
            
        } catch (Exception e) {
            e.printStackTrace();
        }

        return u;
    }
    
    public boolean insertarUsuario(Usuario u) {
        boolean insertado = false;

        try {
            String sql = "INSERT INTO Usuario "
                    + "(nombre, apellido1, apellido2, dni, direccion, fecha_nacimiento, email, usuario, clave) "
                    + "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            PreparedStatement ps = conexion.prepareStatement(sql);

            ps.setString(1, u.getNombre());
            ps.setString(2, u.getApellido1());
            ps.setString(3, u.getApellido2());
            ps.setString(4, u.getDni());
            ps.setString(5, u.getDireccion());

            // Conversión de java.util.Date en java.sql.Date
            if (u.getFechaNacimiento() != null) {
                ps.setDate(6, new java.sql.Date(u.getFechaNacimiento().getTime()));
            } else {
                ps.setDate(6, null);
            }

            ps.setString(7, u.getEmail());
            ps.setString(8, u.getUsuario());
            ps.setString(9, u.getClave());

            int filas = ps.executeUpdate();
            insertado = (filas > 0);

            ps.close();                
            
        } catch (Exception e) {
            e.printStackTrace();
            insertado = false;
        }

        return insertado;
    }
    
    public boolean existeUsuario(String user) {
        boolean existe = false;

        try {
            String sql = "SELECT 1 FROM Usuario WHERE usuario = ?";
            PreparedStatement ps = conexion.prepareStatement(sql);
            ps.setString(1, user);

            ResultSet rs = ps.executeQuery();
            existe = rs.next();

            rs.close();
            ps.close();
        } catch (Exception e) {
            e.printStackTrace();
        }

        return existe;
    }


}

