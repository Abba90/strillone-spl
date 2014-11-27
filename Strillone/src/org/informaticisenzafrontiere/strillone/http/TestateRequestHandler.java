package org.informaticisenzafrontiere.strillone.http;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Locale;
import java.util.Map;

import android.util.Log;

import org.informaticisenzafrontiere.strillone.MainPresenter;
import org.informaticisenzafrontiere.strillone.R;
import org.informaticisenzafrontiere.strillone.util.App;
import org.informaticisenzafrontiere.strillone.util.Configuration;
import org.informaticisenzafrontiere.strillone.xml.Testata;
import org.informaticisenzafrontiere.strillone.xml.Testate;
import org.informaticisenzafrontiere.strillone.xml.TestateXMLHandler;
import org.informaticisenzafrontiere.strillone.xml.XMLHandler;

public class TestateRequestHandler extends RequestHandler {
	
	private final static String TAG = TestateRequestHandler.class.getSimpleName();

	private MainPresenter mainPresenter;
	
	public TestateRequestHandler(MainPresenter mainPresenter) {
		this.mainPresenter = mainPresenter;
	}

	@Override
	protected String getURL() {
		return "http://192.168.1.132/strillone/feeds/testate.php";
		//return "http://www.walks.to/strillone/feeds/testate.php";
	}

	@Override
	protected Map<String, String> getParameters() {
		return new HashMap<String, String>();
	}
	
	public void onResponseReceived(String response) {
		if ("".equals(response)) {
			this.mainPresenter.notifyErrorDowloadingHeaders(App.getInstance().getString(R.string.connecting_error));
		} else {
			try {
				XMLHandler xmlHandler = new TestateXMLHandler();
				Testate testate = (Testate)xmlHandler.deserialize(response, true);
				
				String lingua = Locale.getDefault().getLanguage();
				List<Testata> lTestateNew = new ArrayList<Testata>();
				List<Testata> lTestate = testate.getTestate();
				for (Testata testata : lTestate) {
					if (lingua.equals(testata.getLingua())) {
						if (Configuration.BETA) {
							// Aggiungi incondizionatamente qualsiasi testata.
							lTestateNew.add(testata);
						} else {
							// Verifica che non sia una testata "beta".
							if (!testata.isBeta()) {
								lTestateNew.add(testata);
							}
						}
						
					}
				}
				testate.setTestate(lTestateNew);
				
				this.mainPresenter.notifyHeadersReceived(testate);
			} catch (Exception e) {
				// this.mainPresenter.notifyCommunicationError(App.getInstance().getString(R.string.connecting_error));
				this.mainPresenter.notifyErrorDowloadingHeaders(App.getInstance().getString(R.string.connecting_error));
			}
		}
	}

}
