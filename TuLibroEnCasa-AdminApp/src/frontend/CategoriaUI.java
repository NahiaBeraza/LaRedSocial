package frontend;

import javax.swing.*;
import javax.swing.table.DefaultTableModel;
import java.awt.*;
import java.awt.event.MouseAdapter;
import java.awt.event.MouseEvent;
import dao.CategoriaDAO;
import model.Categoria;
import java.util.List;

/**
 * Versión estilizada de la ventana CRUD de categorías.
 * Inspirada en el CSS proporcionado.
 */
public class CategoriaUI extends JFrame {

    private static final long serialVersionUID = 1L;

    private CategoriaDAO categoriaDAO = new CategoriaDAO();

    private JTable tabla;
    private DefaultTableModel modeloTabla;

    public CategoriaUI() {

        // ============================
        // CONFIGURACIÓN DE LA VENTANA
        // ============================
        setTitle("Gestión de Categorías");
        setSize(550, 450);
        setLocationRelativeTo(null);
        setDefaultCloseOperation(JFrame.DISPOSE_ON_CLOSE);

        // Fondo morado
        getContentPane().setBackground(Color.decode("#7b6cff"));
        setLayout(new GridBagLayout());

        // ============================
        // CARD PRINCIPAL
        // ============================
        JPanel card = new JPanel();
        card.setBackground(Color.WHITE);
        card.setPreferredSize(new Dimension(480, 380));
        card.setLayout(new BorderLayout(20, 20));
        card.setBorder(BorderFactory.createEmptyBorder(20, 20, 20, 20));

        // ============================
        // TÍTULO
        // ============================
        JLabel titulo = new JLabel(
                "<html><h2 style='color:#6c63ff;'>Gestión de Categorías</h2></html>"
        );
        titulo.setHorizontalAlignment(SwingConstants.CENTER);

        card.add(titulo, BorderLayout.NORTH);

        // ============================
        // TABLA ESTILIZADA
        // ============================
        modeloTabla = new DefaultTableModel(new Object[]{"ID", "Nombre"}, 0);
        tabla = new JTable(modeloTabla);

        tabla.getTableHeader().setBackground(Color.decode("#6c63ff"));
        tabla.getTableHeader().setForeground(Color.WHITE);
        tabla.getTableHeader().setFont(new Font("SansSerif", Font.BOLD, 14));

        tabla.setRowHeight(24);

        JScrollPane scroll = new JScrollPane(tabla);
        card.add(scroll, BorderLayout.CENTER);

        // ============================
        // BOTONES ESTILO CSS
        // ============================
        JPanel panelBotones = new JPanel(new GridLayout(1, 3, 10, 10));
        panelBotones.setBackground(Color.WHITE);

        JButton btnAñadir = crearBoton("Añadir");
        JButton btnModificar = crearBoton("Modificar");
        JButton btnBorrar = crearBoton("Borrar");

        panelBotones.add(btnAñadir);
        panelBotones.add(btnModificar);
        panelBotones.add(btnBorrar);

        card.add(panelBotones, BorderLayout.SOUTH);

        add(card);

        // ============================
        // ACCIONES CRUD
        // ============================
        btnAñadir.addActionListener(e -> añadirCategoria());
        btnModificar.addActionListener(e -> modificarCategoria());
        btnBorrar.addActionListener(e -> borrarCategoria());

        cargarTabla();
        setVisible(true);
    }

    // ============================
    // BOTÓN ESTILIZADO
    // ============================
    private JButton crearBoton(String texto) {
        JButton btn = new JButton(texto);

        btn.setBackground(Color.decode("#6c63ff"));
        btn.setForeground(Color.WHITE);
        btn.setFocusPainted(false);
        btn.setFont(new Font("SansSerif", Font.BOLD, 14));
        btn.setBorder(BorderFactory.createEmptyBorder(10, 10, 10, 10));

        btn.addMouseListener(new MouseAdapter() {
            @Override
            public void mouseEntered(MouseEvent e) {
                btn.setBackground(Color.decode("#584ff5"));
            }

            @Override
            public void mouseExited(MouseEvent e) {
                btn.setBackground(Color.decode("#6c63ff"));
            }
        });

        return btn;
    }

    // ============================
    // CRUD
    // ============================
    private void cargarTabla() {
        modeloTabla.setRowCount(0);
        List<Categoria> categorias = categoriaDAO.listar();

        for (Categoria c : categorias) {
            modeloTabla.addRow(new Object[]{c.getIdCategoria(), c.getNombreCategoria()});
        }
    }

    private void añadirCategoria() {
        String nombre = JOptionPane.showInputDialog(this, "Nombre de la categoría:");

        if (nombre != null && !nombre.isBlank()) {
            categoriaDAO.insertar(new Categoria(nombre));
            cargarTabla();
        }
    }

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
