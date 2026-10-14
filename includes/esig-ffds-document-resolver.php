<?php
/**
 * Resolves which e-signature document an [esigfluent] shortcode belongs to.
 *
 * @package ESIG_FFDS
 */

if ( ! class_exists( 'ESIG_FFDS_Document_Resolver' ) ) :

	/**
	 * Document id resolution for the [esigfluent] shortcode.
	 *
	 * @since 2.0.6
	 */
	class ESIG_FFDS_Document_Resolver {

		/**
		 * Resolve the document the shortcode is rendering for.
		 *
		 * A bare document_id is honoured only for the logged-in document owner or a
		 * manage_options admin; anyone else needs a validated invite hash + checksum
		 * pair (TRL-1772).
		 *
		 * @since 2.0.6
		 *
		 * @return int|string|null Document id, or null when none can be verified.
		 */
		public static function resolve() {
			$document_id = self::from_rendering_context();

			if ( empty( $document_id ) ) {
				$document_id = self::from_invite_pair( esigget( 'invite' ), esigget( 'csum' ) );
			}

			if ( empty( $document_id ) ) {
				$document_id = self::from_authorized_request( esigget( 'document_id' ) );
			}

			if ( empty( $document_id ) ) {
				$document_id = get_option( 'esig_global_document_id' );
			}

			return empty( $document_id ) ? null : $document_id;
		}

		/**
		 * Accept a requested document id only from an authorized e-signature user.
		 *
		 * Mirrors core's preview rule (TRL-1769): the document owner or a
		 * manage_options admin, so owners can preview their own documents.
		 *
		 * @since 2.0.6
		 *
		 * @param string|int|null $document_id Requested document id.
		 *
		 * @return int|null Document id when the current user may view it, otherwise null.
		 */
		public static function from_authorized_request( $document_id ) {
			$document_id = absint( $document_id );
			$user_id     = get_current_user_id();

			if ( ! $document_id || ! $user_id || ! function_exists( 'WP_E_Sig' ) ) {
				return null;
			}

			$owner_id = (int) WP_E_Sig()->document->get_document_owner_id( $document_id );
			if ( ! $owner_id ) {
				return null; // Document does not exist.
			}

			if ( ! current_user_can( 'manage_options' ) && $owner_id !== $user_id ) {
				return null;
			}

			return $document_id;
		}

		/**
		 * Return the document currently being rendered by core, if any.
		 *
		 * @since 2.0.6
		 * @access private
		 *
		 * @return int|null Document id or null.
		 */
		private static function from_rendering_context() {
			if ( class_exists( '\WpEsignature\Models\Document' ) && method_exists( '\WpEsignature\Models\Document', 'current_document_id' ) ) {
				return \WpEsignature\Models\Document::current_document_id();
			}

			return null;
		}

		/**
		 * Resolve a normal document from a validated invite hash + checksum pair.
		 *
		 * The invite must exist, belong to the document, and the checksum must
		 * match that document. Stand Alone Documents are never resolved here.
		 *
		 * @since 2.0.6
		 *
		 * @param string|null $invite_hash Invite hash from the request.
		 * @param string|null $checksum    Document checksum from the request.
		 *
		 * @return int|null Document id when the pair is valid, otherwise null.
		 */
		public static function from_invite_pair( $invite_hash, $checksum ) {
			if ( empty( $invite_hash ) || empty( $checksum ) || ! function_exists( 'WP_E_Sig' ) ) {
				return null;
			}

			$document_id = WP_E_Sig()->invite->getdocumentid_By_invitehash( $invite_hash );
			if ( empty( $document_id ) ) {
				return null;
			}

			$stored_checksum = WP_E_Sig()->document->document_checksum_by_id( $document_id );
			if ( empty( $stored_checksum ) || ! hash_equals( (string) $stored_checksum, (string) $checksum ) ) {
				return null;
			}

			if ( 'normal' !== WP_E_Sig()->document->getDocumentType( $document_id ) ) {
				return null;
			}

			return (int) $document_id;
		}
	}

endif;
