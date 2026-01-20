package frontendSimple;

import javax.swing.*;
import javax.swing.table.DefaultTableModel;
import java.awt.*;
import dao.AutorDAO;
import model.Autor;
import java.util.List;

/**
 * Ventana CRUD simple para gestionar autores.
 * Versión básica sin estilos, ideal para aprender Swing.
 */
public class AutorUISimple extends JFrame {

    private static final long serialVersionUID = 1L;

    private AutorDAO autorDAO = new AutorDAO();

    private JTable tabla;
    private DefaultTableModel modeloTabla;

    public AutorUISimple() {

        setTitle("Gestión de Autores");
        setSize(400, 300);
        setLocationRelativeTo(null);
        setDefaultCloseOperation(JFrame.DISPOSE_ON_CLOSE);

        setLayout(new BorderLayout());

        // TABLA
        modeloTabla = new DefaultTableModel(new Object[]{"ID", "Nombre"}, 0);
        tabla = new JTable(modeloTabla);

        add(new JScrollPane(tabla), BorderLayout.CENTER);

        // BOTONES CRUD
        JPanel panelBotones = new JPanel(new FlowLayout());

        JButton btnAñadir = new JButton("Añadir");
        JButton btnModificar = new JButton("Modificar");
        JButton btnBorrar = new JButton("Borrar");

        panelBotones.add(btnAñadir);
        panelBotones.add(btnModificar);
        panelBotones.add(btnBorrar);

        add(panelBotones, BorderLayout.SOUTH);

        // ACCIONES
        btnAñadir.addActionListener(e -> añadirAutor());
        btnModificar.addActionListener(e -> modificarAutor());
        btnBorrar.addActionListener(e -> borrarAutor());

        cargarTabla();
        setVisible(true);
    }

    private void cargarTabla() {
        modeloTabla.setRowCount(0);
        List<Autor> autores = autorDAO.listar();

        for (Autor a : autores) {
            modeloTabla.addRow(new Object[]{a.getIdAutor(), a.getNombreAutor()});
        }
    }

    private void añadirAutor() {
        String nombre = JOptionPane.showInputDialog(this, "Nombre del autor:");

        if (nombre != null && !nombre.isBlank()) {
            autorDAO.insertar(new Autor(nombre));
            cargarTabla();
        }
    }

    private void modificarAutor() {
        int fila = tabla.getSelectedRow();

        if (fila == -1) {
            JOptionPane.showMessageDialog(this, "Selecciona un autor.");
            return;
        }

        int id = (int) tabla.getValueAt(fila, 0);
        String nombreActual = (String) tabla.getValueAt(fila, 1);

        String nuevoNombre = JOptionPane.showInputDialog(this, "Nuevo nombre:", nombreActual);

        if (nuevoNombre != null && !nuevoNombre.isBlank()) {
            Autor autor = autorDAO.buscar(id);
            autor.setNombreAutor(nuevoNombre);
            autorDAO.modificar(autor);
            cargarTabla();
        }
    }

    private void borrarAutor() {
        int fila = tabla.getSelectedRow();

        if (fila == -1) {
            JOptionPane.showMessageDialog(this, "Selecciona un autor.");
            return;
        }

        int id = (int) tabla.getValueAt(fila, 0);

        int confirm = JOptionPane.showConfirmDialog(
                this,
                "¿Seguro que deseas borrar este autor?",
                "Confirmar borrado",
                JOptionPane.YES_NO_OPTION
        );

        if (confirm == JOptionPane.YES_OPTION) {
            boolean borrado = autorDAO.borrar(id);

            if (!borrado) {
                JOptionPane.showMessageDialog(this, "No se puede borrar (puede tener libros asociados).");
            }

            cargarTabla();
        }
    }
}
