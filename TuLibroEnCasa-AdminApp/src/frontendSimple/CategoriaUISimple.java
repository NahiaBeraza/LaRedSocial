package frontendSimple;

import javax.swing.*;
import javax.swing.table.DefaultTableModel;
import java.awt.*;
import dao.CategoriaDAO;
import model.Categoria;
import java.util.List;

/**
 * Ventana CRUD simple para gestionar categorías.
 * Versión básica sin estilos, ideal para aprender Swing.
 */
public class CategoriaUISimple extends JFrame {

    private static final long serialVersionUID = 1L;

    private CategoriaDAO categoriaDAO = new CategoriaDAO();

    private JTable tabla;
    private DefaultTableModel modeloTabla;

    public CategoriaUISimple() {

        // ================================
        // CONFIGURACIÓN DE LA VENTANA
        // ================================
        setTitle("Gestión de Categorías");
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
        btnAñadir.addActionListener(e -> añadirCategoria());
        btnModificar.addActionListener(e -> modificarCategoria());
        btnBorrar.addActionListener(e -> borrarCategoria());

        cargarTabla();
        setVisible(true);
    }

    // ================================
    // CARGAR TABLA
    // ================================
    private void cargarTabla() {
        modeloTabla.setRowCount(0);
        List<Categoria> categorias = categoriaDAO.listar();

        for (Categoria c : categorias) {
            modeloTabla.addRow(new Object[]{c.getIdCategoria(), c.getNombreCategoria()});
        }
    }

    // ================================
    // AÑADIR
    // ================================
    private void añadirCategoria() {
        String nombre = JOptionPane.showInputDialog(this, "Nombre de la categoría:");

        if (nombre != null && !nombre.isBlank()) {
            categoriaDAO.insertar(new Categoria(nombre));
            cargarTabla();
        }
    }

    // ================================
    // MODIFICAR
    // ================================
    private void modificarCategoria() {
        int fila = tabla.getSelectedRow();

        if (fila == -1) {
            JOptionPane.showMessageDialog(this, "Selecciona una categoría.");
            return;
        }

        int id = (int) tabla.getValueAt(fila, 0);
        String nombreActual = (String) tabla.getValueAt(fila, 1);

        String nuevoNombre = JOptionPane.showInputDialog(this, "Nuevo nombre:", nombreActual);

        if (nuevoNombre != null && !nuevoNombre.isBlank()) {
            Categoria categoria = categoriaDAO.buscar(id);
            categoria.setNombreCategoria(nuevoNombre);
            categoriaDAO.modificar(categoria);
            cargarTabla();
        }
    }

    // ================================
    // BORRAR
    // ================================
    private void borrarCategoria() {
        int fila = tabla.getSelectedRow();

        if (fila == -1) {
            JOptionPane.showMessageDialog(this, "Selecciona una categoría.");
            return;
        }

        int id = (int) tabla.getValueAt(fila, 0);

        int confirm = JOptionPane.showConfirmDialog(
                this,
                "¿Seguro que deseas borrar esta categoría?",
                "Confirmar borrado",
                JOptionPane.YES_NO_OPTION
        );

        if (confirm == JOptionPane.YES_OPTION) {
            boolean borrado = categoriaDAO.borrar(id);

            if (!borrado) {
                JOptionPane.showMessageDialog(this, "No se puede borrar (puede tener libros asociados).");
            }

            cargarTabla();
        }
    }
}

