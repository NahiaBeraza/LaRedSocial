package frontend;

import javax.swing.*;
import javax.swing.table.DefaultTableModel;
import java.awt.*;
import java.awt.event.MouseAdapter;
import java.awt.event.MouseEvent;
import dao.EditorialDAO;
import model.Editorial;
import java.util.List;

/**
 * Versión estilizada de la ventana CRUD de editoriales.
 * Inspirada en el CSS proporcionado.
 */
public class EditorialUI extends JFrame {

    private static final long serialVersionUID = 1L;

    private EditorialDAO editorialDAO = new EditorialDAO();

    private JTable tabla;
    private DefaultTableModel modeloTabla;

    public EditorialUI() {

        // ============================
        // CONFIGURACIÓN DE LA VENTANA
        // ============================
        setTitle("Gestión de Editoriales");
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
                "<html><h2 style='color:#6c63ff;'>Gestión de Editoriales</h2></html>"
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
        btnAñadir.addActionListener(e -> añadirEditorial());
        btnModificar.addActionListener(e -> modificarEditorial());
        btnBorrar.addActionListener(e -> borrarEditorial());

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
        List<Editorial> editoriales = editorialDAO.listar();

        for (Editorial e : editoriales) {
            modeloTabla.addRow(new Object[]{e.getIdEditorial(), e.getNombreEditorial()});
        }
    }

    private void añadirEditorial() {
        String nombre = JOptionPane.showInputDialog(this, "Nombre de la editorial:");

        if (nombre != null && !nombre.isBlank()) {
            editorialDAO.insertar(new Editorial(nombre));
            cargarTabla();
        }
    }

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
