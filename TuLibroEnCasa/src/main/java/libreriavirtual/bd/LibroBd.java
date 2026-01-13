package libreriavirtual.bd;

import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;
import java.util.List;

import libreriavirtual.beans.Libro;

public class LibroBd extends BdBase {

    public LibroBd() {
        super();
    }

    public List<Libro> buscarLibros(String titulo, String autor, String categoria,
                                    String logica, String orden) {

        List<Libro> lista = new ArrayList<>();

        try {
            // Consulta con JOINs reales. StringBuilder es para construir cadenas dinámicas,se puede hacer con String, pero StringBuilder es más limpio y eficiente cuando vas concatenando muchas partes.
            StringBuilder sql = new StringBuilder(
                "SELECT l.*, a.nombre_autor, c.nombre_categoria, e.nombre_editorial " +
                "FROM Libro l " +
                "JOIN Autor a ON l.id_autor = a.id_autor " +
                "JOIN Categoria c ON l.id_categoria = c.id_categoria " +
                "JOIN Editorial e ON l.id_editorial = e.id_editorial " +
                "WHERE 1=1 "
            );
            
            // Lista de parámetros,guardamos los valores que irán en los ? del PreparedStatement. Así podemos construir el SQL por un lado y los parámetros por otro.
            List<Object> params = new ArrayList<>();

            // Asegurar que logica tenga un valor válido ANTES de usarla (tiene que estar al principo, antes de cualquier filtro
            if (logica == null || (!logica.equalsIgnoreCase("AND") && !logica.equalsIgnoreCase("OR"))) {
                logica = "AND";
            }

            // Filtro por título
            if (titulo != null && !titulo.trim().isEmpty()) {
                sql.append(" ").append(logica).append(" l.titulo LIKE ?");
                params.add("%" + titulo + "%");
            }

            // Filtro por autor
            if (autor != null && !autor.trim().isEmpty()) {
                sql.append(" ").append(logica).append(" a.nombre_autor LIKE ?");
                params.add("%" + autor + "%");
            }

            // Filtro por categoría
            if (categoria != null && !categoria.equals("cualquiera")) {
                sql.append(" ").append(logica).append(" c.nombre_categoria LIKE ?");
                params.add("%" + categoria + "%");
            }


            // Orden
            if (orden != null) {
                if (orden.equals("titulo")) {
                    sql.append(" ORDER BY l.titulo");
                } else if (orden.equals("autor")) {
                    sql.append(" ORDER BY a.nombre_autor");
                }
            }
            
          //Convertimos el StringBuilder a String
            PreparedStatement ps = conexion.prepareStatement(sql.toString());

            // Asignar parámetros
            for (int i = 0; i < params.size(); i++) {
                ps.setObject(i + 1, params.get(i));
            }

            ResultSet rs = ps.executeQuery();

            while (rs.next()) {
                Libro l = new Libro();

                // Campos principales
                l.setIsbn(rs.getString("isbn"));
                l.setTitulo(rs.getString("titulo"));
                l.setPrecioUnitario(rs.getDouble("precio_unitario"));
                l.setStock(rs.getInt("stock"));

                // IDs
                l.setIdAutor(rs.getInt("id_autor"));
                l.setIdCategoria(rs.getInt("id_categoria"));
                l.setIdEditorial(rs.getInt("id_editorial"));

                // Nombres reales
                l.setNombreAutor(rs.getString("nombre_autor"));
                l.setNombreCategoria(rs.getString("nombre_categoria"));
                l.setNombreEditorial(rs.getString("nombre_editorial"));

                lista.add(l);
            }

            rs.close();
            ps.close();

        } catch (Exception e) {
            e.printStackTrace();
        }

        return lista;
    }
    
    public void restarStock(String isbn, int cantidad) {

        String sql = "UPDATE libro SET stock = stock - ? WHERE isbn = ?";

        try {
            PreparedStatement ps = conexion.prepareStatement(sql);
            ps.setInt(1, cantidad);
            ps.setString(2, isbn);

            ps.executeUpdate();

        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

}
