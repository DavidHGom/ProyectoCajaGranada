package org.hopto.fundacioncgr.fundacioncgr.ConexionServidor;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Intent;
import android.content.SharedPreferences;
import android.os.AsyncTask;
import android.preference.PreferenceManager;
import android.util.Log;

import com.google.gson.Gson;
import com.google.gson.JsonArray;
import com.google.gson.JsonObject;
import com.google.gson.JsonParser;
import com.google.gson.JsonSyntaxException;

import org.apache.commons.io.IOUtils;
import org.hopto.fundacioncgr.fundacioncgr.App;
import org.hopto.fundacioncgr.fundacioncgr.AudioPlayer;
import org.hopto.fundacioncgr.fundacioncgr.FullscreenVideoPlayer;
import org.hopto.fundacioncgr.fundacioncgr.PantallaPrincipal;
import org.hopto.fundacioncgr.fundacioncgr.Visualizacion_Texto;
import org.hopto.fundacioncgr.fundacioncgr.sqlite.SQLWrapper;
import org.json.JSONArray;

import java.io.BufferedInputStream;
import java.io.BufferedOutputStream;
import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.io.OutputStreamWriter;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLConnection;
import java.security.cert.CertificateFactory;
import java.util.List;

import javax.net.ssl.HttpsURLConnection;


/**
 * Clase para manejar todas las conexiones a la API del sistema.
 */
public class Conexion extends AsyncTask<String, Void, Object> {
    private String base_url_api;
    private String base_url;
    private Activity launcher;
    private JsonParser parser;
    private String comando;
    public static final String CONTENIDO = "CONTENIDO";
    public static final String SALAS = "SALAS";
    public static final String DOWNLOAD_STR = "DOWNLOAD_STR";
    public static final String RECORRIDOS = "RECORRIDOS";
    public static final String CONTENIDOS = "CONTENIDOS";
    public static final String ASISTENCIA = "ASISTENCIA";
    public static final String SUGERENCIA = "SUGERENCIA";
    public static final String COMPARTIR = "COMPARTIR";
    public static final String ELEMENTOS = "ELEMENTOS";

    /**
     * Constructor para crear la conexion.
     * @param launcher Actividad que crear la conexion.
     */
    public Conexion(Activity launcher) {
        base_url_api = "http://fundacioncgr.hopto.org/api";
        base_url = "http://fundacioncgr.hopto.org";
        parser = new JsonParser();
        comando = "";
        this.launcher = launcher;
    }

    /**
     * Método que se ejecuta al lanzar el execute del objeto como una hebra a parte.
     * @param params Parametros que se pasan al execute, el primero siempre debe de ser el tipo de acción.
     * @return Object, dependiendo de la acción solicitada será un String o un JsonArray.
     */
    @Override
    protected Object doInBackground(String... params) {
        Object info = null;
        comando = params[0];
        switch (comando) {
            case CONTENIDO:
                info = getContenido(params[1], params[2]);
                break;

            case ASISTENCIA:
                info = crearAsistencia(params[1]);
                break;

            case SALAS:
                info = getSala();
                break;

            case RECORRIDOS:
                info = getRecorridos();
                break;

            case CONTENIDOS:
                info = getListaContenidos();
                break;

            case ELEMENTOS:
                info = getListaElementos(params[1]);
                break;

            case DOWNLOAD_STR:
                info = downloadSubttiles(params[1]);
                break;

            case COMPARTIR:
                info = compartirGuia(params[1]);
                break;

            case SUGERENCIA:
                info = enviarSugerencia(params[1], params[2]);
        }

        return info;
    }

    /**
     * Método para enviar una guía personalizada a la api y sea almacenada en el servidor.
     * @param json json con dos campos "titulo" que contenga el título de la guía y "recursos" que es un array con los codigos de los recursos a relacionar.
     * @return codigo de la guía devuelto por la api.
     */
    private String compartirGuia(String json) {
        String url_api = base_url_api + "/guia/personalizada/";
        HttpURLConnection conexion = null;
        InputStream input = null;
        BufferedWriter output = null;
        String result = "-1";

        try {
            conexion =(HttpURLConnection) new URL(url_api).openConnection();
            conexion.setDoInput (true);
            conexion.setDoOutput (true);
            conexion.setRequestMethod("POST");

            conexion.connect();
            output = new BufferedWriter(new OutputStreamWriter(conexion.getOutputStream(), "UTF-8"));

            output.write(json);
            output.flush();
            output.close();

            input = new BufferedInputStream(conexion.getInputStream());
            String response = readStream(input);
            JsonObject obj = (JsonObject) parser.parse(response);

            int res  = obj.get("res").getAsInt();
            if(res != -1) {
                result = obj.get("codigo").getAsString();
            }

        } catch (IOException e) {
            e.printStackTrace();
        }

        return result;
    }

    /**
     * Método para enviar una sugerencia
     * @param nombre Parámetro con el nombre de la persona que envía la sugerencia
     * @param mensaje Parámetro que contiene el mensaje con la sugerencia
     * @return -1 en caso de error y 1 en caso de envío satisfactorio
     */
    private int enviarSugerencia(String nombre, String mensaje){
        int result = -1;
        String url_api = base_url_api + "/sugerencias";
        HttpURLConnection conexion = null;
        InputStream input = null;
        BufferedWriter output = null;

        try {
            conexion =(HttpURLConnection) new URL(url_api).openConnection();
            conexion.setDoInput (true);
            conexion.setDoOutput (true);
            conexion.setRequestMethod("POST");

            JsonObject ob = new JsonObject();
            ob.addProperty("nombre", nombre);
            ob.addProperty("mensaje", mensaje);

            String json = ob.toString();

            conexion.connect();
            output = new BufferedWriter(new OutputStreamWriter(conexion.getOutputStream(), "UTF-8"));

            output.write(json);
            output.flush();
            output.close();

            input = new BufferedInputStream(conexion.getInputStream());
            String response = readStream(input);
            JsonObject res = (JsonObject) parser.parse(response);

            result = res.get("res").getAsInt();

        } catch (IOException e) {
            e.printStackTrace();
        }

        return result;
    }

    /**
     * Método para crear una asistencia nueva en el sistema a través de la API.
     * @param codigo código del recurso cercano donde se pide la asistencia.
     * @return -1 en caso de error, 1 en caso satisfactorio.
     */
    private int crearAsistencia(String codigo) {
        int result = -1;
        String url_api = base_url_api + "/asistencia/pedir";
        HttpURLConnection conexion = null;
        InputStream input = null;
        BufferedWriter output = null;

        try {
            conexion =(HttpURLConnection) new URL(url_api).openConnection();
            conexion.setDoInput (true);
            conexion.setDoOutput (true);
            conexion.setRequestMethod("POST");

            JsonObject ob = new JsonObject();
            ob.addProperty("codigo", Integer.valueOf(codigo));

            String json = ob.toString();

            conexion.connect();
            output = new BufferedWriter(new OutputStreamWriter(conexion.getOutputStream(), "UTF-8"));

            output.write(json);
            output.flush();
            output.close();

            input = new BufferedInputStream(conexion.getInputStream());
            String response = readStream(input);
            JsonObject res = (JsonObject) parser.parse(response);

            result = res.get("res").getAsInt();

        } catch (IOException e) {
            e.printStackTrace();
        }

        return result;
    }

    /**
     * Método que descarga los subtítulos de un vídeo y los guarda en caché
     * @param url Parámetro con la url donde se encuentran alojados los subtítulos
     * @return File, Devuelve un archivo con los subtítulos
     */
    private Object downloadSubttiles(String url) {

        HttpURLConnection conexion = null;
        InputStream input = null;
        OutputStream output = null;
        File downloadingFile = null;

        try {
            URL url_conexion = new URL(url);
            conexion = (HttpURLConnection) url_conexion.openConnection();
            conexion.connect();

            // download the file
            input = conexion.getInputStream();
            downloadingFile = new File(launcher.getCacheDir(), "tmp.srt");
            output = new FileOutputStream(downloadingFile);

            byte data[] = new byte[4096];
            int count;
            while ((count = input.read(data)) != -1) {
                // allow canceling with back button
                if (isCancelled()) {
                    input.close();
                    return null;
                }
                output.write(data, 0, count);
            }
        } catch (Exception e) {
            return e.toString();
        } finally {
            try {
                if (output != null)
                    output.close();
                if (input != null)
                    input.close();
            } catch (IOException ignored) {
            }

            if (conexion != null)
                conexion.disconnect();
        }
        return downloadingFile;
    }

    /**
     * Método que devuelve las salas del museo mediante una consulta a la API.
     * @return JsonArray con la información de cada sala del museo.
     */
    private Object getSala(){
        String url_api = base_url_api + "/sala";


        String json_contenido = "";
        Object info = null;

        try {
            HttpURLConnection conexion = (HttpURLConnection) new URL(url_api).openConnection();

            InputStream input = new BufferedInputStream(conexion.getInputStream());
            json_contenido = readStream(input);
            JsonObject obj_json = parser.parse(json_contenido).getAsJsonObject();
            info = obj_json.get("content").getAsJsonArray();

        } catch (IOException e) {
            e.printStackTrace();
        } catch (IllegalStateException e) {

        }

        return info;

    }

    /** 
     * Método que devuelve las guías predefinidas del museo y las personalizadas que el usuario ha señalado
     * @return Lista de recorridos
     */
    private Object getRecorridos(){

        SQLWrapper wrapper = new SQLWrapper(launcher);

        List<String> guias = wrapper.getGuiasPersonalizadas();

        String url_api = base_url_api + "/guia/tipo/predefinida";

        String json_contenido = "";
        Object info = null;
        try {
            HttpURLConnection conexion = (HttpURLConnection) new URL(url_api).openConnection();

            InputStream input = new BufferedInputStream(conexion.getInputStream());
            json_contenido = readStream(input);
            JsonObject obj_json = parser.parse(json_contenido).getAsJsonObject();
            JsonArray recorridos = obj_json.get("content").getAsJsonArray();

            JsonObject obj = null;
            for (String code : guias) {
                obj = this.getRecorridoPersonalizado(code);
                if(obj != null)
                    recorridos.add(obj);
            }

            info = recorridos;

        } catch (IOException e) {
            e.printStackTrace();
        } catch (IllegalStateException e) {

        }



        return info;
    }

    /**
     * Método que localiza una guía personalizada concreta
     * @param code Parametro con el código del recorrido
     * @return La guía personalizada que coincide con el código
     */
    private JsonObject getRecorridoPersonalizado(String code){

        String url_api = base_url_api + "/guia/personalizada/" + code;

        String json_contenido = "";
        JsonObject info = null;
        try {
            HttpURLConnection conexion = (HttpURLConnection) new URL(url_api).openConnection();

            InputStream input = new BufferedInputStream(conexion.getInputStream());
            json_contenido = readStream(input);
            JsonObject obj_json = parser.parse(json_contenido).getAsJsonObject();

            if(obj_json.get("res").getAsInt() != -1)
                info = obj_json.get("content").getAsJsonObject();

        } catch (IOException e) {
            e.printStackTrace();
        } catch (IllegalStateException e) {

        }

        return info;
    }
    /**
     * Método para obtener todos los recursos del museo. Se usa para mostrar los contenidos al crear un recorrido.
     * @return JsonArray con todos los contenidos del museo obtenidos de la API.
     */
    private Object getListaContenidos(){
        String url_api = base_url_api + "/recurso";


        String json_contenido = "";
        Object info = null;

        try {
            HttpURLConnection conexion = (HttpURLConnection) new URL(url_api).openConnection();

            InputStream input = new BufferedInputStream(conexion.getInputStream());
            json_contenido = readStream(input);
            JsonObject obj_json = parser.parse(json_contenido).getAsJsonObject();
            info = obj_json.get("content").getAsJsonArray();

        } catch (IOException e) {
            e.printStackTrace();
        } catch (IllegalStateException e) {

        }

        return info;
    }

    /**
     * Metodo para obtener todos los elementos que contiene una guía
     * @param codigo Parámetro con el código de identificación de un aguía
     * @return Lista de contenidos de la guía
     */
    private Object getListaElementos(String codigo){
        String url_api = base_url_api + "/guia/composicion/"+codigo;


        String json_contenido = "";
        Object info = null;

        try {
            HttpURLConnection conexion = (HttpURLConnection) new URL(url_api).openConnection();

            InputStream input = new BufferedInputStream(conexion.getInputStream());
            json_contenido = readStream(input);
            JsonObject obj_json = parser.parse(json_contenido).getAsJsonObject();
            info = obj_json.get("content").getAsJsonArray();

        } catch (IOException e) {
            e.printStackTrace();
        } catch (IllegalStateException e) {

        }

        return info;
    }

    /**
     * Método para obtener la url o texto del contenido asignado a un recurso con un código concreto y en un formato específico.
     * @param codigo Identificación del recurso.
     * @param formato formato multimedia o texto en el que se desea obtener el contenido.
     * @return url o texto del contenido solicitado.
     */
    private String getContenido(String codigo, String formato) {

        String idioma = ((App)launcher.getApplicationContext()).getLang();

        String url_api = base_url_api + "/contenido/"
                + codigo + "/" + formato + "/" + idioma;

        String json_contenido = "";
        String info = "";
        String base = (formato.equals("texto")) ? "":base_url;

        try {
            HttpURLConnection conexion = (HttpURLConnection) new URL(url_api).openConnection();

            InputStream input = new BufferedInputStream(conexion.getInputStream());
            json_contenido = readStream(input);
            JsonObject obj_json = parser.parse(json_contenido).getAsJsonObject();
            info = base + obj_json.get("content").getAsJsonObject().get("info").getAsString();
        } catch (IOException e) {
            e.printStackTrace();
        } catch (IllegalStateException e) {

        } catch (JsonSyntaxException e) {

        }


        return info;
    }

    /**
     * Método para obtener y transformar la información del inputStream de la conexión
     * @param in Parámetro inputStream de la conexión
     * @return Información
     */
    private String readStream(InputStream in) {
        StringBuilder sb = new StringBuilder();
        try {
            BufferedReader r = new BufferedReader(new InputStreamReader(in), 1000);
            for (String line = r.readLine(); line != null; line = r.readLine()) {
                sb.append(line);
            }
            in.close();
        } catch (IOException e) {

        }
        return sb.toString();
    }
}
