package org.hopto.fundacioncgr.fundacioncgr;

import android.app.Application;
import android.content.SharedPreferences;
import android.content.res.Configuration;
import android.preference.PreferenceManager;

import java.util.Locale;

/**
 * Clase que contiene el contexto global de la aplicación y alamacena las configuraciones.
 */
public class App extends Application {

    private Locale locale = null;

    /**
     * Método para menejar un cambio en la configuración
     * @param newConfig Parámetro que representa todas las configuraciones actuales.
     */
    @Override
    public void onConfigurationChanged(Configuration newConfig) {
        super.onConfigurationChanged(newConfig);
        if (locale != null) {
            Locale.setDefault(locale);
            Configuration config = new Configuration(newConfig);
            config.setLocale(locale);
            getBaseContext().getResources().updateConfiguration(config, getBaseContext().getResources().getDisplayMetrics());
        }
    }

    /**
     * Método para crear una configuración por defecto al iniciar la aplicación por primera vez
     */
    @Override
    public void onCreate() {
        super.onCreate();
        SharedPreferences settings = PreferenceManager.getDefaultSharedPreferences(this);
        String l = Locale.getDefault().getLanguage();
        String lang = settings.getString(getString(R.string.locale_lang), l);

        String pref = settings.getString("pref", "texto");
        changePref(pref);

        changeLang(lang);
    }

    /**
     * Método para cambiar el idioma en la configuración
     * @param lang Parámetro que contiene un idioma
     */
    public void changeLang(String lang) {
        Configuration config = getBaseContext().getResources().getConfiguration();
        if (!"".equals(lang) && !config.locale.getLanguage().equals(lang)) {

            SharedPreferences.Editor ed = PreferenceManager.getDefaultSharedPreferences(this).edit();
            ed.putString(getString(R.string.locale_lang), lang);
            ed.commit();

            locale = new Locale(lang);
            Locale.setDefault(locale);
            Configuration conf = new Configuration(config);
            conf.setLocale(locale);
            getBaseContext().getResources().updateConfiguration(conf, getBaseContext().getResources().getDisplayMetrics());
        }
    }

    /**
     * Método para cambiar las preferencias de accesibilidad
     * @param pref Parámetro que contiene un tipo de preferencia de accesibilidad
     */
    public void changePref(String pref) {

        if (!"".equals(pref)) {
            SharedPreferences.Editor ed = PreferenceManager.getDefaultSharedPreferences(this).edit();
            ed.putString("pref", pref);
            ed.commit();
        }
    }


    /**
     * Método para obtener el idioma actual de la configuración
     * @return El idioma
     */
    public String getLang(){
        String l = Locale.getDefault().getLanguage();
        return PreferenceManager.getDefaultSharedPreferences(this).getString(this.getString(R.string.locale_lang), l);
    }

    /**
     * Método que devuelve las preferencias de accesibilidad actuales de la configuración
     * @return La preferencia
     */
    public String getPref(){
        return PreferenceManager.getDefaultSharedPreferences(this).getString("pref", "texto");
    }



}
