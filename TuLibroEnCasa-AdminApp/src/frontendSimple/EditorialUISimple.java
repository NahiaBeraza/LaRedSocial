package frontendSimple;

import javax.swing.*;
import javax.swing.table.DefaultTableModel;
import java.awt.*;
import dao.EditorialDAO;
import model.Editorial;
import java.util.List;

/**
 * Ventana CRUD simple para gestionar editoriales.
 * Versión básica sin estilos, ideal para aprender Swing.
 */
public class EditorialUISimple extends JFrame {

    private static final long serialVersionUID = 1L;

    private EditorialDAO editorialDAO = new EditorialDAO();

    private JTable tabla;
    private DefaultTableModel modeloTabla;

    public EditorialUISimple() {

        // ================================
        // CONFIGURACIÓN DE LA VENTANA
        // ================================
        setTitle("Gestión de Editoriales");
        setSize(400, 300);
        setLocationRelativeTo(null);
        setDefaultCloseOperation(JFrame.DISPOSE_ON_CLOSE);

        setLayout(new BorderLayout());

        // ================================
        // TABLA
        // ================================
        modeloTabla = new DefaultTableModel(new Object[]{"ID", "Nombre"}, 0);
        tabla = new JTable(modeloTabla);

        add(new JScrollPane(tabla), BorderLayout.CENTER);

        // ================================
        // BOTONES CRUD
        // ================================
        JPanel panelBotones = new JPanel(new FlowLayout());

        JButton btnAñadir = new JButton("Añadir");
        JButton btnModificar = new JButton("Modificar");
        JButton btnBorrar = new JButton("Borrar");

        panelBotones.add(btnAñadir);
        panelBotones.add(btnModificar);
        panelBotones.add(btnBorrar);

        add(panelBotones, BorderLayout.SOUTH);

        // ================================
        // ACCIONES
        // ================================
        btnAñadir.addActionListener(e -> añadirEditorial());
        btnModificar.addActionListener(e -> modificarEditorial());
        btnBorrar.addActionListener(e -> borrarEditorial());

        cargarTabla();
        setVisible(true);
    }

    // ================================
    // CARGAR TABLA
    // ================================
    private void cargarTabla() {
        modeloTabla.setRowCount(0);
        List<Editorial> editoriales = editorialDAO.listar();

        for (Editorial e : editoriales) {
            modeloTabla.addRow(new Object[]{e.getIdEditorial(), e.getNombreEditorial()});
        }
    }

    // ================================
    // AÑADIR
    // ================================
    private void añadirEditorial() {
        String nombre = JOptionPane.showInputDialog(this, "Nombre de la editorial:");

        if (nombre != null && !nombre.isBlank()) {
            editorialDAO.insertar(new Editorial(nombre));
            cargarTabla();
        }
    }

    // ================================
    // MODIFICAR
    // ================================
    private void modificarEditorial() {
        int fila = tabla.getSelectedRow();

        if (fila == -1) {
            JOptionPane.showMessageDialog(this, "Selecciona una editorial.");
            return;
        }

        int id = (int) tabla.getValueAt(fila, 0);
        String nombreActual = (String) tabla.getValueAt(fila, 1);

        String nuevoNombre = JOptionPane.showInputDialog(this, "Nuevo nombre:", nombreActual);

        if (nuevoNombre != null && !nuevoNombre.isBlank()) {
            Editorial editorial = editorialDAO.buscar(id);
            editorial.setNombreEditorial(nuevoNombre);
            editorialDAO.modificar(editorial);
            cargarTabla();
        }
    }

    // ================================
    // BORRAR
    // ================================
    private void borrarEditorial() {
        int fila = tabla.getSelectedRow();

        if (fila == -1) {
            JOptionPane.showMessageDialog(this, "Selecciona una editorial.");
            return;
        }

        int id = (int) tabla.getValueAt(fila, 0);

        int confirm = JOptionPane.showConfirmDialog(
                this,
                "¿Seguro que deseas borrar esta editorial?",
                "Confirmar borrado",
                JOptionPane.YES_NO_OPTION
        );

        if (confirm == JOptionPane.YES_OPTION) {
            boolean borrado = editorialDAO.borrar(id);

            if (!borrado) {
                JOptionPane.showMessageDialog(this, "No se puede borrar (puede tener libros asociados).");
            }

            cargarTabla();
        }
    }
}
