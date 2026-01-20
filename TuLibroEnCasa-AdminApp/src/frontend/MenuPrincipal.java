package frontend;

import javax.swing.*;
import java.awt.*;
import java.awt.event.MouseAdapter;
import java.awt.event.MouseEvent;

public class MenuPrincipal extends JFrame {
    
    private static final long serialVersionUID = 1L;
    
    public MenuPrincipal() {

        // ============================
        // CONFIGURACIÓN DE LA VENTANA
        // ============================
        setTitle("Librería Virtual");
        setSize(500, 500);
        setLocationRelativeTo(null);
        setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);

        // Fondo morado
        getContentPane().setBackground(Color.decode("#7b6cff"));
        setLayout(new GridBagLayout()); // Centra el card

        // ============================
        // CARD PRINCIPAL
        // ============================
        JPanel card = new JPanel();
        card.setBackground(Color.WHITE);
        card.setPreferredSize(new Dimension(380, 420));
        card.setLayout(new GridLayout(5, 1, 15, 15)); // 5 elementos
        card.setBorder(BorderFactory.createCompoundBorder(
                BorderFactory.createLineBorder(new Color(0, 0, 0, 40), 1),
                BorderFactory.createEmptyBorder(30, 30, 30, 30)
        ));

        // ============================
        // TÍTULO
        // ============================
        JLabel titulo = new JLabel(
            "<html><h1 style='color:#6c63ff; text-align:center;'>Librería Virtual</h1></html>",
            SwingConstants.CENTER
        );

        // ============================
        // BOTONES
        // ============================
        JButton btnAutores = crearBoton("Gestionar autores");
        JButton btnCategorias = crearBoton("Gestionar categorías");
        JButton btnEditoriales = crearBoton("Gestionar editoriales");
        JButton btnLibros = crearBoton("Gestionar libros");

        // Acciones
        btnAutores.addActionListener(e -> new AutorUI());
        btnCategorias.addActionListener(e -> new CategoriaUI());
        btnEditoriales.addActionListener(e -> new EditorialUI());
        btnLibros.addActionListener(e -> new LibroUI());

        // Añadir al card
        card.add(titulo);
        card.add(btnAutores);
        card.add(btnCategorias);
        card.add(btnEditoriales);
        card.add(btnLibros);

        // Añadir card al centro
        add(card);

        setVisible(true);
    }

    // ============================================================
    // BOTÓN ESTILIZADO
    // ============================================================
    private JButton crearBoton(String texto) {
        JButton btn = new JButton(texto);

        btn.setBackground(Color.decode("#6c63ff"));
        btn.setForeground(Color.WHITE);
        btn.setFocusPainted(false);
        btn.setFont(new Font("SansSerif", Font.BOLD, 15));

        btn.setBorder(BorderFactory.createCompoundBorder(
                BorderFactory.createLineBorder(new Color(0, 0, 0, 0), 1),
                BorderFactory.createEmptyBorder(14, 14, 14, 14)
        ));

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

    // ============================================================
    // MAIN ADAPTADO CON FONDO A PANTALLA COMPLETA
    // ============================================================
    public static void main(String[] args) {

        // Ventana de fondo a pantalla completa
        JFrame fondo = new JFrame();
        fondo.setUndecorated(true);
        fondo.setExtendedState(JFrame.MAXIMIZED_BOTH);
        fondo.getContentPane().setBackground(Color.LIGHT_GRAY); // o Color.WHITE
        fondo.setVisible(true);

        // Ventana principal centrada encima
        MenuPrincipal ventana = new MenuPrincipal();
        ventana.setLocationRelativeTo(null);
        ventana.setVisible(true);

        // Cuando cierres la ventana principal, cierra también el fondo
        ventana.addWindowListener(new java.awt.event.WindowAdapter() {
            @Override
            public void windowClosing(java.awt.event.WindowEvent e) {
                fondo.dispose();
            }
        });
    }
}
